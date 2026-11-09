/*
 * <eso-editor>
 *
 * A Tiptap editor on the HTML its view rendered, sanitized, into [data-slot="editor-content"],
 * writing what data-format allows and offering the tools it lists (its features, palette,
 * fonts, …, from the server). Livewire owns the HTML value; the hidden native input
 * forwards model events and modifiers. Change is reported when editing ends.
 *
 * Controls: [data-editor-command] (and data-editor-value) runs a command and shows whether
 * it is on (aria-pressed, aria-checked in menus) or possible; [data-editor-action] opens
 * what the editor has besides (media, find, source, full screen). The toolbar is one line
 * by default, or wraps when More is omitted, with one tab stop and arrow keys within it; one tooltip
 * names whichever control is pointed at.
 *
 * Media selection, dropped and pasted files, and image imports emit ui-editor-media-request.
 *
 * Built into dist/editor.js; Alpine loads it on demand.
 */

import { Editor, createDocument } from '@tiptap/core'
import BubbleMenu from '@tiptap/extension-bubble-menu'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import Typography from '@tiptap/extension-typography'
import { CharacterCount, Placeholder } from '@tiptap/extensions'
import { closeHistory } from '@tiptap/pm/history'
import { Slice } from '@tiptap/pm/model'
import { NodeSelection, TextSelection } from '@tiptap/pm/state'
import { CellSelection } from '@tiptap/pm/tables'
import StarterKit from '@tiptap/starter-kit'
import { marked } from 'marked'
import { contentOf } from '../alpine/overlay.js'
import { clone, readTemplates } from '../support/templates.js'
import { COMMANDS } from './editor/commands.js'
import { MoveBlocks, dragHandle } from './editor/drag.js'
import { Find, findKey } from './editor/find.js'
import { Saving, dataFile, savingKey, unsaved, unsavedImages, wordImages } from './editor/images.js'
import { accepted, attachmentNode, kindOf, safeUrl, videoNode } from './editor/media.js'
import { Audio, PlainCodeBlock, Video, callout, collapsible, embed, figures, file, formulas, imageRows, picture, tables, tasks, toMathML } from './editor/nodes.js'
import { wordLists } from './editor/paste.js'
import { blockStyle, styleExtensions } from './editor/styles.js'
import { suggestionMenu } from './editor/suggest.js'
import { overflow } from './editor/toolbar.js'
import { uploads, uploadsKey } from './editor/uploads.js'
import { generic, passthrough, whitelist } from './editor/whitelist.js'

const MAC = /Mac|iPhone|iPad/.test(navigator.platform ?? '')
const MATH = ['inlineMath', 'blockMath']
const TOOLTIP_CONTROL = 'button:has(> [data-editor-tip])'

// The nodes and marks that keep their whitelisted style (and other attributes), and the
// marks that keep only other attributes.
const STYLED = ['paragraph', 'heading', 'blockquote', 'bulletList', 'orderedList', 'listItem', 'taskList', 'taskItem', 'codeBlock', 'horizontalRule', 'details', 'detailsSummary', 'detailsContent', 'table', 'tableRow', 'tableCell', 'tableHeader', 'image', 'video', 'audio', 'file', 'embed', 'figure', 'figcaption', 'callout', 'textStyle', 'highlight', 'link']
const MARKS = ['bold', 'italic', 'underline', 'strike', 'code', 'superscript', 'subscript']
const ROVE = ['ArrowLeft', 'ArrowRight', 'Home', 'End']

// What arrow keys move to in More (its rows) and in a grid of swatches.
const STEPS = { ArrowLeft: [0, -1], ArrowRight: [0, 1], ArrowUp: [-1, 0], ArrowDown: [1, 0], Home: [0, -Infinity], End: [0, Infinity] }

// An auto popover is open: Escape is its, to close it.
const popoverOpen = () => !! document.querySelector('[popover]:not([popover="manual"]):popover-open')

// Text that reads as Markdown: headings, lists, quotes, fences, tables, emphasis, links.
const MARKDOWN = /^(#{1,6}\s|\s*[-*+]\s|\s*\d+[.)]\s|>\s|```|\|.*\|\s*$)|\*\*\S[^*]*\*\*|\[[^\]]+\]\([^)\s]+\)/m

// One grapheme per character, so 汉字 and 👍🏽 count one each; spaces and line breaks
// none, nor media (Tiptap gives each a space): as the server counts, RichText::characters().
const segmenter = typeof Intl.Segmenter === 'function' ? new Intl.Segmenter() : null
const characters = text => {
    text = text.replace(/\s+/g, '')

    return segmenter ? [...segmenter.segment(text)].length : [...text].length
}

// Mod-B: ⌘B on Apple systems, Ctrl+B elsewhere; and as aria-keyshortcuts.
const ariaKeys = shortcut => shortcut.split('-').map(key => key === 'Mod' ? (MAC ? 'Meta' : 'Control') : key.length === 1 ? key.toUpperCase() : key).join('+')

// The HTML a block to a line, for the source view.
const lines = html => html.replace(/(<\/(?:p|h[1-6]|li|ul|ol|blockquote|pre|table|tr|thead|tbody|colgroup|details|summary|div|iframe|video|audio)>|<(?:hr|br|col|img)[^>]*>|<(?:ul|ol|table|tbody|tr|details)[^>]*>)(?!\n)/g, '$1\n').trim()

const focused = (view, element) => view.hasFocus() || element.contains(document.activeElement)

// Touch screens show their own menu over a selection: the bubbles go under it there.
const TOUCH = typeof matchMedia === 'function' && matchMedia('(pointer: coarse)').matches

const MEDIA = ['image', 'video', 'audio', 'file']

let uploaded = 0
let mediaRequests = 0

import { listen, unlisten, emit, disconnected } from '../alpine/interaction.js'

export default (binding = {}) => ({

    _editor: null,
    _focused: null,
    _reported: '',
    _format: null,
    _whitelist: null,
    _labels: {},
    _saving: new Set(),
    _uploading: 0,
    _refused: false,
    _saved: new Map(),
    _failed: new Map(),
    _seenImages: new Set(),
    _unsaved: { doc: null, images: [] },
    _hosts: [],
    _rtf: '',
    _painter: null,
    _toolbar: null,
    _toolbarMorph: [],
    _requests: new Set(),
    _tracked: new Set(),
    _mediaRequest: null,
    _selected: null,
    _anchor: null,
    _context: '',
    _menus: [],
    _mathPreview: 0,
    _panelStatus: {},
    _panelSnapshot: '',
    _panelTimer: null,
    _panelRequest: null,
    _tipTimer: null,
    _bubbleScrollTimer: null,
    _uploadErrors: new Map(),
    _uploadProgress: new Map(),


    init() {

        if (this._editor) {
            listen(this, window, 'ui-editor', event => this._server(event))

            return
        }

        const content = this.$el.querySelector(':scope > [data-slot="editor-content"]')

        if (! content) return

        this._format = { features: [], headings: [1, 2, 3, 4, 5, 6], colors: [], highlights: [], fonts: [], sizes: [], lineHeights: [], indents: 8, platforms: [], styles: {}, ...JSON.parse(this.$el.dataset.format || '{}') }
        this._whitelist = whitelist(this._format)
        this._hosts = (this.$el.dataset.imageHosts ?? '').split(/\s+/).filter(Boolean)

        const html = content.innerHTML
        const labels = this._labels = JSON.parse(this.$el.dataset.labels || '{}')

        content.replaceChildren()

        this._editor = new Editor({
            element: content,
            content: html,
            editable: ! this.$el.hasAttribute('data-disabled'),
            extensions: this._extensions(),
            editorProps: {
                attributes: () => ({
                    'data-slot': 'editor-input',
                    'role': 'textbox',
                    'aria-multiline': 'true',
                    'aria-label': this.$el.dataset.label ?? '',
                    ...this.$el.hasAttribute('data-labelledby') ? { 'aria-labelledby': this.$el.dataset.labelledby } : {},
                    // How to reach the toolbar and the bubble menus from the text.
                    'aria-description': labels.keyboard ?? '',
                    ...this.$el.dataset.placeholder ? { 'aria-placeholder': this.$el.dataset.placeholder } : {},
                    ...this.$el.hasAttribute('data-disabled') ? { 'aria-disabled': 'true' } : {},
                    ...this._aria(),
                }),
                // On phones the toolbar is over the keyboard: the cursor stays clear of it.
                scrollMargin: TOUCH ? { top: 8, right: 8, bottom: 64, left: 8 } : 5,
                // Word's pictures come in its RTF, read as the paste or drop begins.
                transformPastedHTML: html => this._whitelist.restrict(wordLists(this._has('image') ? wordImages(html, this._rtf) : html)),
                handlePaste: (view, event, slice) => this._paste(view, event, slice),
                handleDrop: (view, event, slice, moved) => this._drop(view, event, moved),
                handleDOMEvents: {
                    paste: (view, event) => this._readRtf(event.clipboardData),
                    drop: (view, event) => this._readRtf(event.dataTransfer),
                    pointerup: () => this._paint(),
                    keyup: () => this._paint(),
                },
            },
            onUpdate: () => {
                const value = this.value


                // A change that leaves the value as it was (the paragraph kept after a last
                // table or image) isn't one.
                if (value === this._reported) return

                this._reported = value
                if (!this.publish(value)) this.$dispatch('input')
                queueMicrotask(() => this._autoSave())
            },
            onFocus: () => {
                this._focused ??= this.value
            },
            onBlur: ({ event }) => {
                // Into the toolbar, a menu or a form of the editor's is still editing.
                if (event?.relatedTarget && this.$el.contains(event.relatedTarget)) return
                this._changed()
            },
            onTransaction: ({ transaction, appendedTransactions }) => {
                for (const request of [...this._requests, ...this._tracked]) {
                    request.from = transaction.mapping.map(request.from)
                    request.to = transaction.mapping.map(request.to)
                }
                this._mapMediaRequest(transaction)
                for (const appended of appendedTransactions) this._mapMediaRequest(appended)

                this._sync()
            },
        })

        // What keeps the document in shape (a paragraph after a last table or image) is
        // done now, not on the first change: on phones Tiptap focuses the editor in the
        // middle of a command, and the document changing then would fail the command.
        this._editor.view.dispatch(this._editor.state.tr.setMeta('addToHistory', false).setMeta('preventUpdate', true))

        this._reported = this.value
        this._seenImages = new Set(this._unsavedImages())
        this._listen()
        this.$el.toggleAttribute('data-editor-mac', MAC)
        const form = this.$el.closest('form')
        if (form) listen(this, form, 'submit', event => {
            if (!this.busy) return
            event.preventDefault(); event.stopImmediatePropagation(); this._refused = true; this._syncBusy()
        }, { capture: true })
        this._prepareControls(this.$el)

        this._layoutToolbar()
        const hook = window.Livewire?.hook
        if (hook) {
            this._toolbarMorph = [
                hook('morph', ({ el }) => {
                    if (!el.contains(this.$el)) return
                    this._tip(null)
                    this._toolbar?.restore()
                }),
                hook('morphed', ({ el }) => {
                    if (!el.contains(this.$el)) return
                    this.$el.toggleAttribute('data-editor-mac', MAC)
                    this._prepareControls(this.$el)
                    this._layoutToolbar()
                    this._sync(true)
                }),
            ]
        }

        this._sync()
        if (binding.property) this.$watch('$wire.' + binding.property, value => this.restore(value))
        this._attributes = new MutationObserver(records => records.forEach(record => this.attributeChangedCallback(record.attributeName)))
        this._attributes.observe(this.$el, { attributes: true, attributeFilter: ['aria-invalid', 'aria-describedby', 'data-disabled', 'data-label', 'data-labelledby'] })
    },

    publish(value) {
        const input = this.$el.querySelector('[data-editor-model]')
        if (input) input.value = value
        const native = input && [...input.attributes].some(attribute => attribute.name.startsWith('wire:model'))
        if (binding.property) this.$wire.$set(binding.property, value, !native && binding.live && !binding.blur)
        if (native) emit(input, 'input', value)
        return native
    },

    destroy() {
        this._mediaRequest = null
        clearTimeout(this._panelTimer)
        clearTimeout(this._tipTimer)
        clearTimeout(this._bubbleScrollTimer)
        this._tip(null)
        disconnected(this)
        this._attributes?.disconnect()
        this._fullscreen(false)
        this._keyboard(false)
        this._toolbar?.disconnect()
        this._toolbarMorph.forEach(dispose => dispose?.())
        this._editor?.destroy()
        this._editor = null
    },

    attributeChangedCallback(name) {
        if (! this._editor) return

        if (name === 'data-disabled') {
            const disabled = this.$el.hasAttribute('data-disabled')
            if (disabled) this._mediaRequest = null
            this._editor.setEditable(! disabled)
        }

        // Refresh the text box's label and validation attributes after morphing.
        this._editor.view.setProps({})
        this._sync()
    },

    get editor() {
        return this._editor
    },

    // The HTML, without the empty paragraph kept last to write after a table or image.
    get value() {
        if (! this._editor) return this.$el.querySelector('[data-slot="editor-content"]')?.innerHTML.trim() ?? ''

        return this._editor.isEmpty ? '' : this._editor.getHTML().replace(/<p><\/p>$/, '')
    },

    // From the server (wire:model): its HTML replaces ours, without reporting back, the
    // cursor staying where it was. The same content written differently (the server's
    // sanitized copy) leaves it be.
    restore(value) {
        value ??= ''

        if (! this._editor || value === this.value || this._holds(value)) return
        this._mediaRequest = null
        for (const request of this._requests) this._placeholder(request.id)
        this._requests.clear()

        const { from, to } = this._editor.state.selection
        const near = (doc, position) => doc.resolve(Math.min(position, doc.content.size))

        this._editor.chain()
            .setContent(value, { emitUpdate: false })
            .command(({ tr }) => !! tr.setSelection(TextSelection.between(near(tr.doc, from), near(tr.doc, to))))
            .run()
        this._reported = this.value
        this._unsavedImages().forEach(src => this._seenImages.add(src))
        const input = this.$el.querySelector('[data-editor-model]')
        if (input) input.value = this.value
    },

    // Whether the editor has this content, give or take its last empty paragraph.
    _holds(html) {
        const incoming = createDocument(html, this._editor.schema)
        const { doc } = this._editor.state
        const last = doc.lastChild

        if (incoming.eq(doc)) return true

        return doc.childCount > 1 && last.type.name === 'paragraph' && ! last.content.size && incoming.eq(doc.copy(doc.content.cut(0, doc.content.size - last.nodeSize)))
    },

    /**
     * Inserts attachments where the cursor is: images, audio and video as themselves, the
     * rest as files.
     */
    insert(attachments) {
        this._insert(attachments, null)
    },

    /**
     * Inserts HTML where the cursor is (a template, a reply), as far as the format allows.
     */
    insertContent(html) {
        this._editor?.chain().focus().insertContent(this._whitelist.restrict(html)).run()
    },

    // A feature of the format: what can be written, or a tool.
    _has(feature) {
        return this._format.features.includes(feature)
    },

    _extensions() {
        const has = feature => this._has(feature)
        const templates = readTemplates(this.$el)
        const lists = has('bullet-list') || has('ordered-list')
        const max = Number(this.$el.dataset.maxLength) || null
        const labels = { expand: 'Expand', collapse: 'Collapse', task: 'Done', caption: '', ...JSON.parse(this.$el.dataset.labels || '{}') }
        const list = this._whitelist
        const styles = this._format.styles

        return [
            // What the whitelist keeps of every node and mark, and of the tags no feature
            // writes.
            passthrough(list, STYLED, MARKS),
            generic(list),
            // A paragraph is kept after a last table or image, to write after it; the value
            // leaves it out.
            StarterKit.configure({
                heading: has('heading') && { levels: this._format.headings },
                bold: has('bold') && {},
                italic: has('italic') && {},
                underline: has('underline') && {},
                strike: has('strike') && {},
                code: has('code') && {},
                link: has('link') && { openOnClick: false, autolink: true, defaultProtocol: 'https' },
                bulletList: has('bullet-list') && {},
                orderedList: has('ordered-list') && {},
                listItem: lists && {},
                listKeymap: (lists || has('task-list')) && {},
                blockquote: has('blockquote') && {},
                horizontalRule: has('horizontal-rule') && {},
                codeBlock: false,
            }),
            // The placeholder on an empty editor's paragraph, a caption's on an empty caption
            // (as the CSS shows them). Asked while the document changes, before editor.state
            // has the new one: positions in it would be out of date.
            Placeholder.configure({
                includeChildren: true,
                placeholder: ({ node }) => node.type.name === 'figcaption' ? labels.caption : this.$el.dataset.placeholder ?? '',
            }),
            styleExtensions(this._format, list),
            has('superscript') && Superscript,
            has('subscript') && Subscript,
            has('task-list') && tasks(labels),
            has('details') && collapsible(labels),
            has('code-block') && PlainCodeBlock,
            has('table') && tables(styles, list),
            has('image') && [picture(list), figures(styles), imageRows(), Saving],
            has('video') && Video,
            has('audio') && Audio,
            has('file') && file(styles.card),
            has('embed') && embed(this._format.platforms),
            has('math') && formulas(),
            has('callout') && callout(styles),
            (templates.uploading || templates['missing-image']) && uploads({ uploading: templates.uploading, failed: templates['upload-failed'], missing: has('image') ? templates['missing-image'] : null }),
            has('find-replace') && Find,
            (has('character-count') || max) && CharacterCount.configure({ limit: max, textCounter: characters }),
            has('typography') && Typography,
            has('drag-handle') && templates['drag-handle'] && dragHandle(this._menu(clone(templates['drag-handle']))),
            has('drag-handle') && MoveBlocks,
            this._suggestion('slash', (_, option) => this._run(option)),
            this._suggestion('emoji', (editor, option) => editor.chain().focus().insertContent(option.dataset.emoji).run()),
            has('bubble-menu') && this._bubbles(templates),
        ].flat(Infinity).filter(Boolean)
    },

    get panel() { return this.$el.querySelector(':scope > [data-editor-panels]') },

    get panelWire() { return this.panel && window.Livewire?.find(this.panel.getAttribute('wire:id')) },

    _suggestion(kind, pick) {
        const menuFor = () => this.panel?.querySelector(`[data-editor-suggestion="${kind}"]`)
        return menuFor() && suggestionMenu(kind, menuFor, (menu, query) => this.panelWire?.search(menu, query), pick)
    },

    _updatePanel(status) {
        if (!this.panel) return
        Object.assign(this._panelStatus, status)
        if (JSON.stringify(this._panelStatus) === this._panelSnapshot || this._panelRequest) return
        clearTimeout(this._panelTimer)
        this._panelTimer = setTimeout(() => {
            if (!this.$el.isConnected) return
            const wire = this.panelWire
            if (!wire) return
            this._panelSnapshot = JSON.stringify(this._panelStatus)
            this._panelRequest = wire.syncStatus(this._panelStatus)
            this._panelRequest.catch(() => {}).finally(() => {
                this._panelRequest = null
                if (this.$el.isConnected) this._updatePanel({})
            })
        }, 150)
    },

    // The bubble menus: for selected text, in a table, on a selected block, on a formula.
    _bubbles(templates) {
        const node = state => state.selection.node?.type.name
        const linkFrame = this.panel?.querySelector('[data-editor-link-frame]')
        const content = this._part('editor-content')
        const menus = {
            text: ({ editor, view, state, from, to, element }) => state.selection instanceof TextSelection && ! state.selection.empty && !! state.doc.textBetween(from, to).trim() && ! editor.isActive('codeBlock') && focused(view, element),
            table: ({ editor, view, state, element }) => editor.isActive('table') && (state.selection.empty || state.selection instanceof CellSelection) && focused(view, element),
            node: ({ view, state, element }) => state.selection instanceof NodeSelection && ! MATH.includes(node(state)) && focused(view, element),
            math: ({ view, state, element }) => MATH.includes(node(state)) && focused(view, element),
            // The cursor in a link: where it goes, to open, change or take off.
            link: ({ editor, view, state, element }) => state.selection.empty && editor.isActive('link') && focused(view, element),
        }

        const available = Object.entries(menus).filter(([name]) => name === 'link' ? linkFrame : templates[`bubble-${name}`])
        if (available.length) {
            listen(this, window, 'scroll', () => {
                if (!this._menus.some(menu => menu.hasAttribute('data-editor-bubble-active'))) return
                clearTimeout(this._bubbleScrollTimer)
                this._bubbleScrollTimer = setTimeout(() => {
                    const view = this._editor?.view
                    if (!view) return
                    const transaction = view.state.tr
                    for (const [name] of available) transaction.setMeta(`bubble-${name}`, 'updatePosition')
                    view.dispatch(transaction)
                }, 60)
            }, { passive: true })
        }

        return available.map(([name, shouldShow]) => {
            const frame = name === 'link' ? linkFrame : null
            const element = this._menu(frame ? frame.firstElementChild : clone(templates[`bubble-${name}`]))
            return BubbleMenu.extend({ name: `bubble-${name}` }).configure({
                pluginKey: `bubble-${name}`,
                element,
                appendTo: frame ?? this._part('editor-bubbles'),
                updateDelay: 100,
                shouldShow: props => props.editor.isEditable && shouldShow(props),
                // The table's menu sits above the table, not the cursor; the others above the
                // selection, or under it on touch screens, clear of their own menu.
                getReferencedVirtualElement: name === 'table' ? () => this._tableRect() : undefined,
                options: {
                    placement: TOUCH && name !== 'table' ? 'bottom' : 'top', offset: 8, flip: {}, shift: { padding: 8 },
                    scrollTarget: content,
                    hide: { boundary: content },
                    onShow: () => {
                        element.setAttribute('data-editor-bubble-active', '')
                        queueMicrotask(() => this._sync(true))
                    },
                    onHide: () => {
                        element.removeAttribute('data-editor-bubble-active')
                        frame?.append(element)
                    },
                },
            })
        })
    },

    _tableRect() {
        const { $from } = this._editor.state.selection

        for (let depth = $from.depth; depth > 0; depth--) {
            if ($from.node(depth).type.name === 'table') {
                const dom = this._editor.view.nodeDOM($from.before(depth))

                return dom instanceof Element ? { getBoundingClientRect: () => dom.getBoundingClientRect(), getClientRects: () => [dom.getBoundingClientRect()] } : null
            }
        }

        return null
    },

    _layoutToolbar() {
        const toolbar = this._part('editor-toolbar')
        if (this._toolbar && toolbar === this._toolbar.toolbar) {
            this._toolbar.layout()
            return
        }
        this._toolbar?.disconnect()
        this._toolbar = toolbar && typeof ResizeObserver === 'function' ? overflow(toolbar, () => this._rovingStart()) : null
    },

    // Announce shortcuts using the modifier of this browser's platform.
    _prepareControls(root) {
        for (const control of root?.querySelectorAll?.('button[data-editor-keys]') ?? []) {
            control.setAttribute('aria-keyshortcuts', ariaKeys(control.dataset.editorKeys))
        }

        return root
    },

    // A bubble menu, kept to update while it is out of the page.
    _menu(element) {
        this._menus.push(this._prepareControls(element))

        return element
    },

    // The elements matching, in the editor and its menus wherever they are.
    _all(selector) {
        return [...new Set([...this.$el.querySelectorAll(selector), ...this._menus.flatMap(menu => [...menu.matches(selector) ? [menu] : [], ...menu.querySelectorAll(selector)])])]
    },

    _listen() {
        listen(this, this.$el, 'click', event => {
            const control = event.target.closest?.('[data-editor-command], [data-editor-action]')

            if (control && ! control.disabled && this.$el.contains(control)) this._run(control)
        })

        listen(this, this.$el, 'keydown', event => this._key(event))
        listen(this, this.$el, 'input', event => this._typed(event))
        listen(this, this.$el, 'change', event => {
            if (event.target.matches?.('[data-editor-alt-input]')) this._setAlt(event.target.value)
        })
        listen(this, this.$el, 'toggle', event => this._opened(event), true)
        listen(this, this.$el, 'emoji-select', event => {
            this._editor.chain().focus().insertContent(event.detail.emoji).run()
        })

        // The tooltip follows the control pointed at or focused.
        listen(this, this.$el, 'pointerover', event => this._tip(event.target.closest?.(TOOLTIP_CONTROL)))
        listen(this, this.$el, 'pointerout', event => ! event.relatedTarget?.closest?.(TOOLTIP_CONTROL) && this._tip(null))
        listen(this, this.$el, 'focusin', event => {
            // A menu can restore focus while its popover is still changing state.
            queueMicrotask(() => this.$el.contains(document.activeElement) && this._tip(document.activeElement.closest?.(TOOLTIP_CONTROL)))
            if (! this._editor.view.dom.contains(event.target)) this._focused ??= this.value
            this._keyboard(this._editor.isEditable)
        })
        listen(this, this.$el, 'focusout', event => {
            if (! event.relatedTarget?.closest?.(TOOLTIP_CONTROL)) this._tip(null)
            if (! this.$el.contains(event.relatedTarget)) this._changed()
            if (! this.$el.contains(event.relatedTarget)) this._keyboard(false)
        })

        listen(this, window, 'ui-editor', event => this._server(event))
    },

    /**
     * On phones, while writing in it: the toolbar over the keyboard (the foot of the
     * visual viewport, which the keyboard shortens), where the thumbs are, not at the
     * top of a page scrolled away; its place kept, so the text doesn't jump.
     */
    _keyboard(on) {
        const toolbar = this._part('editor-toolbar')
        const viewport = window.visualViewport

        if (! TOUCH || ! toolbar || on === this.$el.hasAttribute('data-keyboard')) return

        if (on) {
            this.$el.style.setProperty('--editor-toolbar-space', `${toolbar.offsetHeight}px`)
            this._placeToolbar()
            clearInterval(this._keyboardTimer)
            this._keyboardTimer = setInterval(() => this._placeToolbar(), 100)
        } else {
            clearInterval(this._keyboardTimer)
        }

        this.$el.toggleAttribute('data-keyboard', on)
    },

    _placeToolbar() {
        const viewport = window.visualViewport

        this.$el.style.setProperty('--editor-keyboard', `${viewport ? Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop) : 0}px`)
    },

    // change once editing ends, if it changed anything.
    _changed() {
        if (this._focused !== null && this._focused !== this.value) {
            const input = this.$el.querySelector('[data-editor-model]')
            if (input) { emit(input, 'change', this.value); emit(input, 'blur', this.value) }
            else this.$dispatch('change')
        }
        this._focused = null
    },

    _server(event) {
        const { name, owner, action, attachments, html, requestId } = event.detail ?? {}

        // Ui::editor(name): this one's name, from the component it is in.
        if (! this._editor || ! name || name !== this.$el.dataset.editorName) return
        if (owner && owner !== this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) return

        if (action === 'insert') this.insert(attachments)
        if (action === 'resolve-media') this._resolveMedia(requestId, attachments)
        if (action === 'insert-content' && html) this.insertContent(html)
        if (action === 'focus') this._editor.commands.focus()
    },

    // What a control does.
    _run(control) {
        const { editorCommand: command, editorValue: value = '', editorAction: action } = control.dataset
        const editor = this._editor

        // A swatch or an item picked in a popover closes it, and More around it.
        const done = () => {
            control.closest?.('[popover]')?.hidePopover()
            control.closest?.('[data-editor-more-content]')?.hidePopover()
        }

        if (command) {
            this._can(command, value, editor.chain().focus())
            done()

            return
        }

        if (['media', 'find', 'source', 'fullscreen', 'format-painter'].includes(action)) done()

        // Media of the button's kind, or the selected one's to replace it.
        if (action === 'media') this._media(value)
        if (action === 'video-apply') this._video(control.closest('[popover]'))
        if (action === 'save-image') this._save([editor.state.selection.node?.attrs.src].filter(Boolean))
        if (action === 'save-images') this._save(this._unsavedImages())
        if (action === 'find') this._find(this._part('editor-find')?.hidden ?? false)
        if (action === 'source') this._source(! this.$el.hasAttribute('data-source'))
        if (action === 'fullscreen') this._fullscreen(! this.$el.hasAttribute('data-fullscreen'))
        if (action === 'link-apply') this._link(control.closest('[popover]'), control.closest('[popover]')?.querySelector('[data-editor-link-input]')?.value ?? '')
        if (action === 'link-remove') this._link(control.closest('[popover]'), '')
        if (action === 'math-apply') this._math(control.closest('[data-editor-math-form]'))
        if (action === 'find-next') editor.commands.findNext(1)
        if (action === 'find-previous') editor.commands.findNext(-1)
        if (action === 'find-case') this._search(control.getAttribute('aria-pressed') !== 'true')
        if (action === 'replace') editor.chain().replace(this._field('editor-replace-input')).findNext(0).run()
        if (action === 'replace-all') editor.commands.replaceAll(this._field('editor-replace-input'))
        if (action === 'find-close') this._find(false)
        if (action === 'format-painter') this._pick(! this._painter)
        if (action === 'dismiss-upload') this._dismiss(value)
        if (action === 'upload-missing') this._uploadMissing(value)
        if (action === 'image-link-apply') this._imageLink(control.closest('[popover]'), control.closest('[popover]')?.querySelector('[data-editor-image-link-input]')?.value ?? '')
        if (action === 'image-link-remove') this._imageLink(control.closest('[popover]'), '')

        // A control in a menu or a popover opens one elsewhere: the toolbar's, More first
        // when it is in there.
        if (action === 'open') {
            const trigger = this.$el.querySelector(`[data-slot="editor-toolbar"] [data-editor-opens="${control.dataset.editorValue}"]`)
            const more = trigger?.closest('[data-editor-more-content]')

            if (more && ! more.matches(':popover-open')) more.showPopover()
            trigger?.click()
        }
    },

    _part(slot) {
        const owner = slot === 'editor-find' ? this.panel : this.$el
        return owner?.querySelector(`:scope > [data-slot="${slot}"]`)
    },

    _field(name) {
        return this.$el.querySelector(`[data-${name}]`)?.value ?? ''
    },

    _key(event) {
        const target = event.target

        // Enter in an editor's field applies it, rather than submitting a form around the editor.
        if (event.key === 'Enter' && ! event.shiftKey && target.matches?.('[data-editor-link-input]')) {
            event.preventDefault()
            this._link(target.closest('[popover]'), target.value)
        }

        if (event.key === 'Enter' && target.matches?.('[data-editor-video-input]')) {
            event.preventDefault()
            this._video(target.closest('[popover]'))
        }

        if (event.key === 'Enter' && target.matches?.('[data-editor-alt-input]')) {
            event.preventDefault()
            this._setAlt(target.value)
            this._editor.commands.focus()
        }

        if (event.key === 'Enter' && (event.metaKey || event.ctrlKey) && target.matches?.('[data-editor-math-input]')) {
            event.preventDefault()
            this._math(target.closest('[data-editor-math-form]'))
        }

        if (target.matches?.('[data-editor-find-input], [data-editor-replace-input]')) {
            if (event.key === 'Enter') {
                event.preventDefault()
                target.matches('[data-editor-find-input]') ? this._editor.commands.findNext(event.shiftKey ? -1 : 1) : this._run({ dataset: { editorAction: 'replace' } })
            }

            if (event.key === 'Escape') {
                event.preventDefault()
                this._find(false)
            }
        }

        // Mod-K: the link's popover, from the toolbar or the bubble menu, whichever shows.
        if ((event.key === 'k' || event.key === 'K') && (MAC ? event.metaKey : event.ctrlKey) && this._editor.view.dom.contains(target)) {
            const link = this._all('[data-editor-opens="link"]').find(control => control.isConnected && ! control.disabled && (control.checkVisibility?.() ?? true))

            event.preventDefault()
            link?.click()
        }

        // Find opens over the browser's own, from the text or the bar.
        if ((event.key === 'f' || event.key === 'F') && (MAC ? event.metaKey : event.ctrlKey) && ! event.altKey && this._part('editor-find') && this.$el.contains(target)) {
            event.preventDefault()
            this._find(true)
        }

        if (event.key === 'Escape' && this._painter) {
            event.preventDefault()
            this._pick(false)
        }

        // Alt+F10: from the text to the bubble menu beside it, else the toolbar; Escape
        // there goes back to the text, where it was.
        if (event.key === 'F10' && event.altKey && this._editor.view.dom.contains(target)) {
            event.preventDefault()
            this._enterBars()
        }

        if (event.key === 'Escape' && ! event.defaultPrevented && ! popoverOpen() && target.closest?.('[data-slot="editor-toolbar"], [data-slot="editor-bubble"]') && ! target.closest('[popover]')) {
            event.preventDefault()
            this._editor.view.focus()
        }

        if (event.key === 'Escape' && this.$el.hasAttribute('data-fullscreen') && ! event.defaultPrevented && ! popoverOpen()) {
            event.preventDefault()
            this._fullscreen(false)
        }

        if (target.matches?.('[data-editor-swatch], [data-editor-spacing-value]') && STEPS[event.key]) this._step(event, [...target.closest('[popover]').querySelectorAll('[role="group"]')].map(group => [...group.querySelectorAll('button')]))

        const popover = target.closest?.('[popover]')
        const bar = popover ? (popover.matches('[data-editor-more-content]') ? popover : null) : target.closest?.('[data-slot="editor-toolbar"], [data-slot="editor-bubble"]')

        if (bar?.matches('[data-editor-more-content]') && STEPS[event.key] && ! target.matches('input, textarea')) this._step(event, [...bar.querySelectorAll('[data-slot="editor-more-group"]')].map(row => this._controlsOf(bar).filter(control => row.contains(control))))
        else if (bar && ROVE.includes(event.key) && ! target.matches('input, textarea')) this._rove(event, bar)
    },

    _typed(event) {
        const target = event.target

        if (target.matches?.('[data-editor-find-input]')) this._search()
        if (target.matches?.('[data-slot="editor-source"]')) this._editor.commands.setContent(this._whitelist.restrict(target.value), { emitUpdate: true })
        if (target.matches?.('[data-editor-math-input], [data-editor-math-display]')) this._previewMath(target.closest('[data-editor-math-form]'))
        if (target.matches?.('[data-editor-video-input]')) target.removeAttribute('aria-invalid')
    },

    // A popover of the editor's opened: its fields start from the selection.
    _opened(event) {
        const popover = event.target

        if (event.newState !== 'open' || ! popover.matches?.('[popover]')) return

        const editor = this._editor
        const link = popover.querySelector('[data-editor-link-input]')
        const video = popover.querySelector('[data-editor-video-input]')
        const math = popover.querySelector('[data-editor-math-form]')
        const imageLink = popover.querySelector('[data-editor-image-link-input]')

        if (imageLink) {
            imageLink.value = editor.state.selection.node?.attrs.href ?? ''
            imageLink.select()
        }

        if (link) {
            link.value = editor.getAttributes('link').href ?? ''
            link.select()
            popover.querySelector('[data-editor-action="link-remove"]')?.toggleAttribute('hidden', ! editor.isActive('link'))
        }

        if (video) {
            video.value = ''
            video.removeAttribute('aria-invalid')
        }

        if (math) this._fillMath(math, '', false)

        this._sync(true)

        // Its first field, else its current choice or first control, takes focus, so typing
        // and arrow keys work at once (menus focus themselves; More, its first control).
        if (popover.matches('[popover="manual"], [role="menu"]') || popover.contains(document.activeElement)) return

        const first = popover.matches('[data-editor-more-content]')
            ? this._controlsOf(popover)[0]
            : popover.querySelector('input:not([type="hidden"]):not([disabled]), textarea') ?? popover.querySelector('[aria-pressed="true"]') ?? popover.querySelector('button:not([disabled])')

        first?.focus()
    },

    // Sets the selection's link to the address, or removes it for none.
    _link(popover, href) {
        const chain = this._editor.chain().focus().extendMarkRange('link')

        href = href.trim()
        ;(href ? chain.setLink({ href }) : chain.unsetLink()).run()
        popover?.hidePopover()
    },

    // A video from its link: a file's address, a platform's share link or embed code.
    _video(popover) {
        const input = popover?.querySelector('[data-editor-video-input]')
        const node = videoNode(input?.value, this._format.features, this._format.platforms)

        if (! node) return input?.setAttribute('aria-invalid', 'true')

        this._editor.chain().focus().insertContent(node).run()
        popover.hidePopover()
        popover.closest('[data-editor-more-content]')?.hidePopover()
    },

    _fillMath(form, latex, display) {
        form.querySelector('[data-editor-math-input]').value = latex
        form.querySelector('[data-editor-math-display]')?.toggleAttribute('checked', display)
        if (form.querySelector('[data-editor-math-display]')) form.querySelector('[data-editor-math-display]').checked = display
        this._previewMath(form)
    },

    // The formula as it will show, or KaTeX's complaint.
    async _previewMath(form) {
        const latex = form.querySelector('[data-editor-math-input]').value.trim()
        const display = !! form.querySelector('[data-editor-math-display]')?.checked
        const preview = form.querySelector('[data-editor-math-preview]')
        const error = form.querySelector('[data-editor-math-error]')
        const token = ++this._mathPreview

        let mathml = null
        let message = ''

        try {
            mathml = latex ? await toMathML(latex, display) : null
        } catch (failure) {
            message = failure.message
        }

        if (token !== this._mathPreview) return

        if (preview) preview.innerHTML = mathml ?? ''
        if (error) error.hidden = !message
        this._updatePanel({ mathError: message })
        form.querySelector('[data-editor-math-input]').toggleAttribute('aria-invalid', !! message)
    },

    // Inserts the form's formula, or replaces the selected one with it.
    async _math(form) {
        if (! form) return

        const latex = form.querySelector('[data-editor-math-input]').value.trim()
        const display = !! form.querySelector('[data-editor-math-display]')?.checked
        let mathml

        if (! latex) return

        try {
            mathml = await toMathML(latex, display)
        } catch {
            return this._previewMath(form)
        }

        const node = { type: display ? 'blockMath' : 'inlineMath', attrs: { latex, mathml } }
        const { selection } = this._editor.state
        const chain = this._editor.chain().focus()

        ;(MATH.includes(selection.node?.type.name) && ! form.closest('[popover]') ? chain.insertContentAt({ from: selection.from, to: selection.to }, node) : chain.insertContent(node)).run()
        form.closest('[popover]')?.hidePopover()
        form.closest('[data-editor-more-content]')?.hidePopover()
    },

    _setAlt(alt) {
        if (this._editor.state.selection.node?.type.name === 'image') this._editor.commands.updateAttributes('image', { alt })
    },

    _media(kind, placeholder = null) {
        const { selection } = this._editor.state
        const operation = kind ? 'insert' : 'replace'
        kind ||= selection.node?.type.name
        const name = this.$el.dataset.editorName
        const { from, to } = selection
        if (! name || ! this._canPickMedia(kind)) return

        const requestId = String(++mediaRequests)
        const multiple = operation === 'insert' && !placeholder
        this._mediaRequest = { requestId, kind, operation, multiple, from, to, placeholder }
        this._mediaError(false)
        this.$dispatch('ui-editor-media-request', { name, requestId, kind, operation, multiple, source: 'picker' })
    },

    _canPickMedia(kind) {
        return this._editor?.isEditable && ! this.$el.hasAttribute('data-disabled') && ! this.$el.hasAttribute('data-source')
            && MEDIA.includes(kind) && this._has(kind) && (this.$el.dataset.mediaPicker ?? '').split(' ').includes(kind)
    },

    _mapMediaRequest(transaction) {
        const request = this._mediaRequest
        if (! request || request.placeholder || ! transaction.docChanged) return

        const from = transaction.mapping.mapResult(request.from, 1)
        if (request.from === request.to) {
            request.from = request.to = from.pos
            return
        }

        const to = transaction.mapping.mapResult(request.to, -1)
        if (request.operation === 'replace' && (from.deleted || to.deleted || transaction.doc.nodeAt(from.pos)?.type.name !== request.kind)) {
            this._mediaRequest = null
            return
        }
        request.from = from.pos
        request.to = Math.max(from.pos, to.pos)
    },

    _resolveMedia(requestId, attachments) {
        const request = this._mediaRequest
        if (! request || request.requestId !== requestId) return
        this._mediaRequest = null
        if (! this._canPickMedia(request.kind) || (Array.isArray(attachments) && !attachments.length)) return

        const { kind, operation, multiple, placeholder } = request
        const nodes = Array.isArray(attachments) && attachments.map(attachment => {
            if (! attachment || (kind !== 'file' && (typeof attachment.mime !== 'string' || kindOf(attachment) !== kind))) return null
            return attachmentNode(attachment, this._format.features, this._format.styles.card, operation === 'replace' && kind === 'file')
        })
        if (! nodes || (! multiple && nodes.length !== 1) || nodes.some(node => ! node)) {
            this._mediaError(true)
            return
        }

        let range = request
        if (placeholder) {
            const position = this._missingImage(placeholder)?.from
            if (position === undefined) return
            range = { from: position, to: position }
        }
        if (this._insertNodes(nodes, range)) {
            if (placeholder) this._placeholder(placeholder)
        } else {
            this._mediaError(true)
        }
    },

    _mediaError(show) {
        const error = this.$el.querySelector(':scope > [data-editor-media-error]')
        if (error) error.hidden = ! show
    },

    /**
     * Uploads files to go at the selection or where they were dropped, as the format allows. A
     * placeholder stands there meanwhile; what uploads goes in its place, in order, any
     * time later, wherever that place has moved to; failures leave a message there.
     * Dismissed, nothing goes there.
     */
    async _uploadFiles(files, range, source) {
        if (! this._canUploadMedia()) return

        const accept = accepted(this._format.features)
        const items = [...files].map(file => ({ file, kind: kindOf({ mime: file.type, url: file.name }) }))
            .filter(({ kind }) => accept.includes(kind) || accept.includes('file'))

        if (! items.length) return

        const request = { from: range.from, to: range.to, id: `upload-${++uploaded}` }
        const pending = []
        const updateProgress = () => {
            const total = pending.reduce((sum, item) => sum + item.size, 0)
            const sent = pending.reduce((sum, item) => sum + item.size * item.share, 0)
            this._progress(request.id, sent / (total || 1))
        }

        this._requests.add(request)
        for (const { file, kind } of items) {
            const item = { size: file.size || 1, share: 0 }
            item.response = this._requestUpload({ source, kind, operation: 'insert', file }, (loaded, total) => {
                if (! Number.isFinite(loaded) || ! Number.isFinite(total) || total <= 0) return
                item.share = Math.max(0, Math.min(1, loaded / total))
                updateProgress()
            })
            if (item.response) pending.push(item)
        }
        if (! pending.length) {
            this._requests.delete(request)
            return
        }

        if (this._requests.has(request)) {
            this._placeholder(request.id, request.from, 'uploading')
            updateProgress()
        }
        this._busy(pending.length)

        const results = await Promise.allSettled(pending.map(item => item.response.finally(() => this._busy(-1))))

        if (! this._requests.delete(request) || ! this._editor) return

        const attachments = results.flatMap(result => result.status === 'fulfilled' && result.value ? [result.value] : [])
        const failure = results.find(result => result.status === 'rejected' && result.reason?.name !== 'AbortError')
        const invalid = results.some(result => result.status === 'fulfilled' && ! result.value)

        // Report failures after inserted files. Cancelled files are skipped.
        this._placeholder(request.id)
        this._tracked.add(request)
        this._insert(attachments, request)
        this._tracked.delete(request)
        if (failure || invalid) this._placeholder(request.id, request.to, 'failed', failure?.reason?.message || this._labels.failed)
    },

    // The upload placeholder by this id: put (of a kind, saying why) where it goes, or
    // taken off for none.
    _placeholder(id, position, kind = null, message = null) {
        if (kind === 'uploading') this._uploadProgress.set(id, 0)
        else this._uploadProgress.delete(id)
        const previous = this._uploadErrors.get(id)
        if (message) this._uploadErrors.set(id, message)
        else this._uploadErrors.delete(id)
        if (previous !== (message ?? undefined)) this._updatePanel({ uploadErrors: [...this._uploadErrors.values()].slice(-8) })
        this._updatePanel({ uploadProgress: [...this._uploadProgress.values()] })
        this._editor?.view.dispatch(this._editor.state.tr.setMeta(uploadsKey, { remove: id, add: kind ? [{ id, position, kind, failed: !!message }] : [] }))
    },

    // The share of an upload placeholder's files sent, as a percentage and a bar.
    _progress(id, share) {
        const placeholder = this._editor?.view.dom.querySelector(`[data-editor-upload="${id}"]`)
        const percent = Math.min(100, Math.round(share * 100))

        if (this._uploadProgress.has(id)) {
            this._uploadProgress.set(id, percent)
            this._updatePanel({ uploadProgress: [...this._uploadProgress.values()] })
        }
        placeholder?.style.setProperty('--editor-upload-progress', `${percent}%`)
        placeholder?.querySelector('[data-editor-upload-progress]')?.setAttribute('aria-valuenow', String(percent))
    },

    // A placeholder dismissed: what it waited for goes nowhere.
    _dismiss(id) {
        for (const request of this._requests) if (request.id === id) this._requests.delete(request)

        this._placeholder(id)
    },

    _canUploadMedia() {
        return this._editor?.isEditable && this.$el.hasAttribute('data-media-upload') && ! this.$el.hasAttribute('data-disabled') && ! this.$el.hasAttribute('data-source')
    },

    // The application may claim a request during dispatch. Only claimed requests become busy.
    _requestUpload(item, progress = null) {
        let response
        let dispatching = true
        this.$dispatch('ui-editor-media-request', {
            name: this.$el.dataset.editorName ?? null,
            requestId: String(++mediaRequests),
            ...item,
            progress,
            respondWith(result) {
                if (dispatching && ! response) response = Promise.resolve(result)
            },
        })
        dispatching = false

        return response?.then(attachment => {
            if (! safeUrl(attachment?.url)) return null

            const { host } = new URL(attachment.url, document.baseURI)

            if (host !== location.host && ! this._hosts.includes(host)) {
                this._hosts.push(host)
                this._unsaved = { doc: null, images: [] }
            }

            return attachment
        })
    },

    _insert(attachments, range) {
        const nodes = (Array.isArray(attachments) ? attachments : [attachments]).filter(Boolean).map(attachment => attachmentNode(attachment, this._format.features, this._format.styles.card)).filter(Boolean)

        return this._insertNodes(nodes, range)
    },

    _insertNodes(nodes, range) {
        if (! this._editor || ! nodes.length) return

        const size = this._editor.state.doc.content.size
        const { from, to } = range ?? this._editor.state.selection

        return this._editor.chain().focus().insertContentAt({ from: Math.min(from, size), to: Math.min(to, size) }, nodes).run()
    },

    // A picture that couldn't be pasted, uploaded where its placeholder stands.
    _uploadMissing(id) {
        if (this._missingImage(id)) this._media('image', id)
    },

    _missingImage(id) {
        return uploadsKey.getState(this._editor.state)?.find(undefined, undefined, spec => spec.id === id && spec.kind === 'missing')[0]
    },

    // The images the app has no copy of, by address, each once (for the footer, kept
    // until the document changes).
    _unsavedImages() {
        const { doc } = this._editor.state

        if (this._unsaved.doc !== doc) this._unsaved = { doc, images: this._has('image') && this.$el.hasAttribute('data-media-upload') ? unsavedImages(doc, this._hosts) : [] }

        return this._unsaved.images
    },

    /**
     * Offers the app a data image as a File, or an external image as a URL.
     * The returned copy replaces all occurrences of the source image.
     */
    _save(sources, automatic = false) {
        if (! this._canUploadMedia()) return

        this._swapImages(new Map(sources.filter(src => this._saved.has(src)).map(src => [src, this._saved.get(src)])))
        const pending = this._saving.size

        for (const src of new Set(sources)) {
            const source = unsaved(src, this._hosts)

            if (! source || this._saving.has(src) || this._saved.has(src)) continue

            if (automatic) this._seenImages.add(src)
            const response = this._requestUpload({
                kind: 'image', source, operation: 'import', automatic,
                ...(source === 'data' ? { file: dataFile(src) } : { url: new URL(src, document.baseURI).href }),
            })
            if (! response) continue

            this._saving.add(src)
            this._failed.delete(src)
            this._busy(1)
            response
                .then(attachment => {
                    if (! attachment) return this._failed.set(src, this._labels.failed)

                    this._saved.set(src, attachment)
                    this._swapImages(new Map([[src, attachment]]))
                })
                .catch(error => {
                    if (error?.name !== 'AbortError') this._failed.set(src, error?.message || this._labels.failed)
                })
                .finally(() => {
                    this._saving.delete(src)
                    this._editor?.commands.setSaving([...this._saving])
                    this._busy(-1)
                })
        }

        if (this._saving.size !== pending) this._editor.commands.setSaving([...this._saving])
    },

    /**
     * Whether files or images are on their way to the uploader: what they will be isn't
     * in the value yet, so a form around the editor doesn't submit meanwhile (the footer
     * says why). aria-busy says so, and editor-busy { busy } when it changes.
     */
    get busy() {
        return this._uploading > 0
    },

    _busy(change) {
        const was = this.busy

        this._uploading = Math.max(0, this._uploading + change)
        this.busy ? this.$el.setAttribute('aria-busy', 'true') : this.$el.removeAttribute('aria-busy')
        if (! this.busy) this._refused = false
        this._syncBusy()

        if (was !== this.busy) this.$dispatch('editor-busy', { busy: this.busy })
    },

    // The footer's word on the uploads: how many, and why the form didn't submit.
    _syncBusy() {
        if (this.panel?.querySelector('[data-editor-busy]')) this._updatePanel({ uploads: this._uploading, refused: this._refused })
    },

    // Offer new images once. Applications choose whether to accept automatic imports.
    _autoSave() {
        if (! this._canUploadMedia()) return

        const sources = this._unsavedImages().filter(src => ! this._seenImages.has(src) && ! this._saved.has(src) && ! this._failed.has(src) && ! this._saving.has(src))

        if (sources.length) this._save(sources, true)
    },

    // The images at these addresses take the app's copies, a step of its own to undo.
    _swapImages(answers) {
        if (! answers.size || ! this._editor) return

        const { tr } = this._editor.state

        tr.doc.descendants((node, position) => {
            const attachment = node.type.name === 'image' && answers.get(node.attrs.src)

            if (attachment) tr.setNodeMarkup(position, undefined, { ...node.attrs, src: attachment.url, alt: node.attrs.alt || attachment.alt || null, width: node.attrs.width ?? attachment.width ?? null, height: node.attrs.height ?? attachment.height ?? null })
        })

        if (tr.docChanged) this._editor.view.dispatch(closeHistory(tr))
    },

    // Sets the selected image's link, or takes it off for none.
    _imageLink(popover, href) {
        href = href.trim()

        if (this._editor.state.selection.node?.type.name === 'image' && (! href || safeUrl(href))) this._editor.chain().focus().updateAttributes('image', { href: href || null }).run()
        popover?.hidePopover()
    },

    /**
     * The format painter: picks up the selection's marks (but links) and its block's
     * style, and puts them on the next text selected, once.
     */
    _pick(on) {
        const { state } = this._editor

        this._painter = on ? { marks: (state.storedMarks ?? state.selection.$from.marks()).filter(mark => mark.type.name !== 'link'), style: blockStyle(this._editor) } : null
        this.$el.toggleAttribute('data-painting', on)
        this._sync()
    },

    _paint() {
        const { state, view } = this._editor ?? {}

        if (! this._painter || ! state || state.selection.empty) return

        const { from, to } = state.selection
        const { tr } = state

        Object.values(state.schema.marks).filter(type => type.name !== 'link').forEach(type => tr.removeMark(from, to, type))
        this._painter.marks.forEach(mark => tr.addMark(from, to, mark))
        state.doc.nodesBetween(from, to, (node, position) => {
            if (! ['paragraph', 'heading'].includes(node.type.name)) return

            tr.setNodeMarkup(position, undefined, { ...node.attrs, style: this._painter.style })

            return false
        })

        view.dispatch(tr)
        this._pick(false)
    },

    // What Word copied as RTF, for the paste or drop under way (its HTML parsed next).
    _readRtf(data) {
        this._rtf = data?.types?.includes?.('text/rtf') ? data.getData('text/rtf') : ''

        return false
    },

    _paste(view, event, slice) {
        const data = event.clipboardData

        this._rtf = ''

        if (! data) return false

        const text = data.getData('text/plain')
        const html = data.getData('text/html')

        // Files alone (a screenshot, files copied in a file manager): uploaded.
        if (data.files.length && ! text && ! html) {
            event.preventDefault()
            this._uploadFiles(data.files, { from: view.state.selection.from, to: view.state.selection.to }, 'paste')

            return true
        }

        // Blocks pasted on an empty line take its place whole, their own style kept,
        // rather than the first and last running into it and taking its style (none). A
        // list's empty item keeps them in the list, as its items.
        const { $from, empty } = view.state.selection

        if (html && empty && $from.parent.isTextblock && ! $from.parent.content.size && slice.openStart && slice.content.firstChild?.isBlock && ! ['listItem', 'taskItem'].includes($from.node(-1).type.name)) {
            view.dispatch(view.state.tr.replaceSelection(new Slice(slice.content, 0, 0)).scrollIntoView().setMeta('paste', true).setMeta('uiEvent', 'paste'))

            return true
        }

        if (html || ! text) return false

        // A video's link alone: its player.
        const video = /^\S+$/.test(text.trim()) ? videoNode(text, this._format.features, this._format.platforms) : null

        if (video) {
            this._editor.chain().focus().insertContent(video).run()

            return true
        }

        if (this._has('markdown') && MARKDOWN.test(text)) {
            this._editor.chain().focus().insertContent(marked.parse(text, { gfm: true, async: false })).run()

            return true
        }

        return false
    },

    _drop(view, event, moved) {
        const files = event.dataTransfer?.files

        this._rtf = ''

        if (moved || ! files?.length) return false

        const position = view.posAtCoords({ left: event.clientX, top: event.clientY })?.pos ?? view.state.selection.from

        // Keep the browser from opening a dropped file.
        event.preventDefault()
        this._uploadFiles(files, { from: position, to: position }, 'drop')

        return true
    },

    _find(open) {
        const bar = this._part('editor-find')

        if (! bar) return

        bar.hidden = ! open
        this.$el.querySelector('[data-editor-action="find"]')?.setAttribute('aria-pressed', String(open))

        if (open) {
            const input = bar.querySelector('[data-editor-find-input]')
            const { from, to } = this._editor.state.selection
            const selected = this._editor.state.doc.textBetween(from, to)

            if (selected && ! selected.includes('\n')) input.value = selected
            input.focus()
            input.select()
            this._search()
        } else {
            this._editor.chain().focus().find('').run()
        }
    },

    _search(caseSensitive) {
        const toggle = this.$el.querySelector('[data-editor-action="find-case"]')

        if (caseSensitive !== undefined) toggle?.setAttribute('aria-pressed', String(caseSensitive))

        this._editor.commands.find(this._field('editor-find-input'), toggle?.getAttribute('aria-pressed') === 'true')
    },

    _source(on) {
        const textarea = this._part('editor-source')
        const content = this._part('editor-content')

        if (! textarea) return
        this._mediaRequest = null

        if (on) textarea.value = lines(this.value)
        else this._editor.commands.setContent(this._whitelist.restrict(textarea.value), { emitUpdate: true })

        this.$el.toggleAttribute('data-source', on)
        textarea.hidden = ! on
        content.hidden = on
        on ? textarea.focus() : this._editor.commands.focus()
        this._sync()
    },

    _fullscreen(on) {
        if (this.$el.hasAttribute('data-fullscreen') === on) return

        this._tip(null)
        this.$el.toggleAttribute('data-fullscreen', on)
        document.documentElement.style.overflow = on ? 'hidden' : ''
        this._sync()
        if (this._editor && ! this.$el.hasAttribute('data-source')) this._editor.commands.focus()
    },

    // The tooltip names the control and its shortcut, anchored to it; none, it hides.
    _tip(control) {
        if (control === this._anchor) return
        clearTimeout(this._tipTimer)
        const open = this._anchor?.querySelector(':scope > [data-editor-tip]:popover-open')
        open?.hidePopover()
        this._anchor = control
        const tip = control?.querySelector(':scope > [data-editor-tip]')
        if (!tip) return

        this._tipTimer = setTimeout(() => {
            if (!tip.isConnected || control.disabled || control.checkVisibility?.() === false) return
            tip.dataset.side = this.$el.hasAttribute('data-fullscreen') && !this.$el.hasAttribute('data-keyboard') ? 'bottom' : 'top'
            tip.showPopover({ source: control })
        }, 150)
    },

    // The bars of controls: the toolbar, and the bubble menus.
    _bars() {
        return [this._part('editor-toolbar'), ...this._menus.filter(menu => menu.matches('[data-slot="editor-bubble"][role="toolbar"]'))].filter(Boolean)
    },

    // A bar's controls that can be focused, minus those in its menus and popovers (More
    // being a bar of its own).
    _controlsOf(bar) {
        const own = bar.matches('[popover]') ? bar : null

        return [...bar.querySelectorAll('button')].filter(button => button.closest('[popover]') === own && ! button.disabled && ! button.closest('[hidden]'))
    },

    // Alt+F10: the bubble menu showing, else the toolbar, at its tab stop.
    _enterBars() {
        const shown = bar => bar.isConnected && ! bar.closest('[hidden]') && getComputedStyle(bar).visibility !== 'hidden'
        const bar = this._bars().find(one => one.matches('[data-slot="editor-bubble"]') && shown(one)) ?? this._part('editor-toolbar')
        const controls = bar ? this._controlsOf(bar) : []

        ;(controls.find(control => control.getAttribute('tabindex') === '0') ?? controls[0])?.focus()
    },

    // One control of each bar is its tab stop: the first, until another is moved to.
    // Those in More are a list of their own, tabbed through.
    _rovingStart() {
        for (const bar of this._bars()) {
            const controls = this._controlsOf(bar)
            const current = controls.find(control => control.getAttribute('tabindex') === '0') ?? controls[0]

            for (const button of bar.querySelectorAll('button')) {
                const popover = button.closest('[popover]')
                const index = button === current ? '0' : '-1'

                if (popover?.matches('[data-editor-more-content]')) button.removeAttribute('tabindex')
                else if (! popover && button.getAttribute('tabindex') !== index) button.setAttribute('tabindex', index)
            }
        }
    },

    // Left and right along a bar, Home and End to its ends; it keeps the tab stop.
    _rove(event, bar) {
        const controls = this._controlsOf(bar)
        const index = controls.indexOf(event.target)
        const next = { ArrowLeft: index - 1, ArrowRight: index + 1, Home: 0, End: controls.length - 1 }[event.key]
        const control = controls[(next + controls.length) % controls.length]

        if (! control) return

        event.preventDefault()
        controls.forEach(button => (button.tabIndex = -1))
        control.tabIndex = 0
        control.focus()
    },

    // Arrow keys in rows (More's, swatches'): along the row, or to the row before or
    // after, as near the same place as it has.
    _step(event, rows) {
        rows = rows.filter(row => row.length)

        const row = rows.findIndex(controls => controls.includes(event.target))
        const [down, across] = STEPS[event.key]
        const target = rows[row + down]

        if (row < 0 || ! target) return

        event.preventDefault()
        target[Math.max(0, Math.min(target.length - 1, rows[row].indexOf(event.target) + across))]?.focus()
    },

    // Runs a command on a chain, or says whether it could; one the format lacks can't.
    _can(name, value, chain) {
        try {
            return COMMANDS[name]?.run(chain, value, this._editor).run() ?? false
        } catch {
            return false
        }
    },

    _aria() {
        return Object.fromEntries(['aria-invalid', 'aria-describedby']
            .filter(name => this.$el.hasAttribute(name))
            .map(name => [name, this.$el.getAttribute(name)]))
    },

    /**
     * The controls show what is on at the selection and what can be done there. What can
     * be done depends on where the selection is, so it is asked again when that changes
     * (or when a menu or popover opens: force), undo and redo every time; controls out of
     * sight wait to be shown, but a menu's items still say which is current.
     */
    _sync(force = false) {
        const editor = this._editor

        if (! editor) return

        const editable = editor.isEditable && ! this.$el.hasAttribute('data-source')
        const { selection } = editor.state
        const path = $position => Array.from({ length: $position.depth + 1 }, (_, depth) => $position.node(depth).type.name).join('/')
        const context = [editable, selection.toJSON().type, selection.empty, selection.node?.type.name, path(selection.$from), path(selection.$to)].join('|')
        const fresh = force || context !== this._context
        const shown = control => control.isConnected && (! control.closest('[popover]') || control.closest('[popover]').matches(':popover-open'))

        this._context = context

        for (const control of this._all('[data-editor-command]')) {
            const name = control.dataset.editorCommand
            const command = COMMANDS[name]
            const value = control.dataset.editorValue ?? ''
            const visible = command && shown(control)

            if (! command || (! visible && control.getAttribute('role') !== 'menuitemradio')) continue

            if (visible && (fresh || name === 'undo' || name === 'redo')) control.disabled = ! editable || ! this._can(name, value, editor.can().chain().focus())
            if (command.active) {
                const active = command.active(editor, value)

                if (control.matches('input[type="radio"]')) control.checked = active
                control.setAttribute(control.getAttribute('role') === 'menuitemradio' ? 'aria-checked' : 'aria-pressed', String(active))
            }
        }

        // Menus show their checked item on their button: its icon, or its name (the menu's
        // own for its default, none).
        for (const trigger of this._all('[data-editor-menu]')) {
            const menu = contentOf(trigger, 'dropdown-menu')
            const checked = menu?.querySelector('[aria-checked="true"]')
            const values = trigger.querySelectorAll('[data-editor-current-value]')

            const label = checked?.dataset.editorLabel ?? checked?.textContent.trim() ?? ''

            trigger.disabled = ! editable
            for (const value of values) value.hidden = value.dataset.editorCurrentValue !== (checked?.dataset.editorValue ?? '')
            // Its name says what it is; what it is set to, said after it.
            if (trigger.getAttribute('aria-description') !== label) label ? trigger.setAttribute('aria-description', label) : trigger.removeAttribute('aria-description')
            for (const icon of trigger.querySelectorAll('[data-editor-icon]')) icon.toggleAttribute('hidden', icon.dataset.editorIcon !== (checked?.dataset.editorValue ?? ''))
        }

        for (const control of this._all('[data-editor-opens], [data-editor-action="media"]')) {
            control.disabled = ! editable || (control.dataset.editorOpens === 'link' && editor.state.selection.empty && ! editor.isActive('link'))
            if (control.dataset.editorOpens === 'link') control.setAttribute('aria-pressed', String(editor.isActive('link')))
        }

        for (const control of this.$el.querySelectorAll('[data-editor-action="find"]')) control.disabled = ! editable

        this.$el.querySelector('[data-editor-action="source"]')?.setAttribute('aria-pressed', String(this.$el.hasAttribute('data-source')))
        this.$el.querySelector('[data-editor-action="format-painter"]')?.setAttribute('aria-pressed', String(!! this._painter))
        this.$el.querySelector('[data-editor-action="fullscreen"]')?.setAttribute('aria-pressed', String(this.$el.hasAttribute('data-fullscreen')))

        this._syncSelection(editor)
        this._syncFind(editor)
        this._syncCount(editor)
        this._syncUnsaved(editor, editable)
        this._rovingStart()
    },

    // The media, formula and link menus' parts and fields, for the node selected or the link.
    _syncSelection(editor) {
        const node = editor.state.selection.node ?? null
        const type = node?.type.name
        const href = editor.getAttributes('link').href ?? ''

        for (const part of this._all('[data-editor-for]')) part.hidden = ! part.dataset.editorFor.split(' ').includes(type)

        // An image the app has no copy of can be saved to it; one that wouldn't says why.
        for (const save of this._all('[data-editor-action="save-image"]')) {
            save.hidden = type !== 'image' || ! unsaved(node.attrs.src, this._hosts)
            save.disabled = ! editor.isEditable || !! savingKey.getState(editor.state)?.pending.has(node?.attrs.src)
        }

        const reason = type === 'image' ? this._failed.get(node.attrs.src) ?? '' : ''
        for (const error of this._all('[data-editor-image-error]')) error.hidden = !reason
        if (this.$el.hasAttribute('data-media-upload')) this._updatePanel({ imageError: reason })

        if (this.panel?.querySelector('[data-editor-link-frame]')) this._updatePanel({ link: href })

        if (node === this._selected) return

        this._selected = node

        const [alt] = this._all('[data-editor-alt-input]')
        const math = this._all('[data-editor-math-form]').find(form => ! form.closest('[popover]'))

        if (alt && type === 'image') alt.value = node.attrs.alt ?? ''
        if (math && MATH.includes(type)) this._fillMath(math, node.attrs.latex, type === 'blockMath')
    },

    _syncFind(editor) {
        const count = this.$el.querySelector('[data-editor-find-count]')
        const state = findKey.getState(editor.state)

        if (! count || ! state) return

        const { matches, current, query } = state

        this._updatePanel({ searching: !!query, matches: matches.length, match: matches.length ? current + 1 : 0 })
        for (const control of this.$el.querySelectorAll('[data-editor-action^="find-"]:not([data-editor-action="find-case"]):not([data-editor-action="find-close"]), [data-editor-action^="replace"]')) {
            control.disabled = ! matches.length || (control.dataset.editorAction.startsWith('replace') && ! editor.isEditable)
        }
    },

    _syncCount(editor) {
        const count = this.$el.querySelector('[data-editor-count]')

        if (! count || ! editor.storage.characterCount) return

        this._updatePanel({ characters: editor.storage.characterCount.characters() })
    },

    // The footer says how many images the app has no copy of, to save them all.
    _syncUnsaved(editor, editable) {
        const notice = this.$el.querySelector('[data-editor-unsaved]')

        if (! notice) return

        const images = this._unsavedImages()
        const saving = savingKey.getState(editor.state)?.pending ?? new Set()
        this._updatePanel({ unsaved: images.length, saving: !editable || images.every(src => saving.has(src)) })
    },
})
