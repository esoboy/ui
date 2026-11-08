/*
 * The Editor's blocks beyond StarterKit, each written as plain HTML that reads anywhere
 * and parsed back from it: no data-* attributes or classes, inline style for the rest.
 * Their style is the whitelisted one they came with (passthrough), or what the editor
 * gives what it creates.
 */

import { Node, mergeAttributes } from '@tiptap/core'
import CodeBlock from '@tiptap/extension-code-block'
import { Details, DetailsContent, DetailsSummary } from '@tiptap/extension-details'
import Image from '@tiptap/extension-image'
import { TaskItem, TaskList } from '@tiptap/extension-list'
import { Table, TableCell, TableHeader, TableRow, createColGroup } from '@tiptap/extension-table'
import { NodeSelection, Plugin, TextSelection } from '@tiptap/pm/state'
import { dataImage } from './images.js'
import { EMBED_STYLE, playable, safeUrl, secure, still } from './media.js'
import { resizable } from './resize.js'
import { computed, restyle } from './styles.js'
import { declarations } from './whitelist.js'

// Attributes the node writes itself, so they don't render as attributes of their own.
const own = (fallback, parseHTML) => ({ default: fallback, parseHTML, renderHTML: () => ({}) })

// A box first in the item (alone or in a label), as Markdown's, GitHub's and Tiptap's are.
const checkbox = item => item.querySelector(':scope > input[type="checkbox"], :scope > label > input[type="checkbox"]')

/**
 * Task lists: <ul> with a box opening each item, styled inline as a list without bullets.
 */
export const tasks = labels => [
    TaskList.extend({
        parseHTML: () => [{ tag: 'ul', priority: 60, getAttrs: list => [...list.children].some(item => item.matches('li') && checkbox(item)) ? null : false }],
        renderHTML: ({ HTMLAttributes }) => ['ul', mergeAttributes(HTMLAttributes, { style: 'list-style-type:none;padding-left:0' }), 0],
    }),
    TaskItem.extend({
        addAttributes: () => ({ checked: { default: false, keepOnSplit: false, parseHTML: item => checkbox(item)?.hasAttribute('checked') ?? false, renderHTML: () => ({}) } }),
        parseHTML: () => [{
            tag: 'li',
            priority: 60,
            getAttrs: item => checkbox(item) ? null : false,
            // Its text: the div after the box, or what is left without the box.
            contentElement: item => {
                const content = item.querySelector(':scope > div')

                if (content) return content

                const rest = item.cloneNode(true)

                checkbox(rest)?.closest('label, input')?.remove()
                if (rest.firstChild instanceof Text) rest.firstChild.textContent = rest.firstChild.textContent.trimStart()

                return rest
            },
        }],
        renderHTML: ({ node, HTMLAttributes }) => ['li', mergeAttributes(HTMLAttributes, { style: 'display:flex;align-items:baseline;gap:0.5em' }), ['input', { type: 'checkbox', disabled: 'disabled', checked: node.attrs.checked ? 'checked' : null }], ['div', 0]],
    }).configure({ nested: true, a11y: { checkboxLabel: node => `${labels.task}: ${node.textContent}` } }),
]

/**
 * Collapsible blocks: native <details> with its <summary>, the rest in a div; closed
 * when shown, whatever they were while writing.
 */
export const collapsible = labels => [
    Details.configure({
        persist: false,
        renderToggleButton: ({ element, isOpen }) => {
            element.setAttribute('aria-label', isOpen ? labels.collapse : labels.expand)
            element.setAttribute('aria-expanded', String(isOpen))
        },
    }),
    DetailsSummary,
    DetailsContent.extend({
        parseHTML: () => [{ tag: 'div[data-type="detailsContent"]' }, { tag: 'details > div' }],
        renderHTML: ({ HTMLAttributes }) => ['div', HTMLAttributes, 0],
    }),
]

// A code block without a language: plain <pre><code>, Tab indenting in it.
export const PlainCodeBlock = CodeBlock.extend({
    addAttributes: () => ({}),
}).configure({ enableTabIndentation: true, tabSize: 4 })

// A cell's column widths: Tiptap's colwidth, else its columns' <col> widths.
function columnWidths(cell) {
    if (cell.getAttribute('colwidth')) return cell.getAttribute('colwidth').split(',').map(Number)

    const columns = [...cell.closest('table')?.querySelectorAll(':scope > colgroup > col') ?? []]
    const span = Number(cell.getAttribute('colspan')) || 1
    let index = 0

    for (const sibling of cell.parentElement?.children ?? []) {
        if (sibling === cell) break
        index += Number(sibling.getAttribute('colspan')) || 1
    }

    const widths = columns.slice(index, index + span).map(column => parseInt(column.style.width, 10))

    return widths.length === span && widths.every(width => width > 0) ? widths : null
}

/**
 * A cell: its whitelisted style, over the editor's border and padding unless it has a
 * border of its own (a table copied from a page drew its borders with a class).
 */
const cell = (extension, style, list) => extension.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            colwidth: own(null, columnWidths),
            style: {
                default: style,
                parseHTML: element => {
                    const kept = list.style(element)

                    return computed(kept, 'border-top-style') ? kept : restyle(style, Object.fromEntries(declarations(kept)))
                },
            },
        }
    },
})

/**
 * Tables: collapsed borders and padded cells inline; column widths on <col>, the table
 * as wide as they add up to (or the page) and scrolling in a div when wider.
 */
export function tables(styles, list) {
    return [
        Table.extend({
            // The div a table scrolls in is the table's own: it isn't kept as a block.
            parseHTML: () => [{ tag: 'div', priority: 60, skip: true, getAttrs: element => element.children.length === 1 && element.firstElementChild.matches('table') ? null : false }, { tag: 'table' }],
            renderHTML({ node, HTMLAttributes }) {
                const { colgroup, tableWidth } = createColGroup(node, this.options.cellMinWidth)

                return ['div', { style: 'overflow-x:auto' }, ['table', mergeAttributes(HTMLAttributes, { style: `border-collapse:collapse;width:${tableWidth || '100%'}` }), colgroup, ['tbody', 0]]]
            },
        }).configure({ resizable: true, cellMinWidth: 48, allowTableNodeSelection: true }),
        TableRow,
        cell(TableHeader, `${styles.cell};${styles.headerCell}`, list),
        cell(TableCell, styles.cell, list),
    ]
}

// The image of a link that holds nothing else.
const linked = link => link.children.length === 1 && link.firstElementChild.matches('img[src]') && ! link.textContent.trim() ? link.firstElementChild : null

// A web address, or a picture in a data: one (pasted, until it is uploaded).
const picturable = src => safeUrl(src) || dataImage(src)

/**
 * Images: on a web address, or data: until uploaded (images.js); a link around one
 * alone is its link. Sized and placed by their style.
 */
export function picture(list) {
    const attributes = image => ({ src: image.getAttribute('src'), alt: image.getAttribute('alt'), title: image.getAttribute('title'), width: image.getAttribute('width'), height: image.getAttribute('height'), style: list.style(image), extras: list.extras(image) })

    return Image.extend({
        addAttributes() {
            return { ...this.parent?.(), href: own(null, () => null) }
        },
        parseHTML: () => [
            { tag: 'a[href]', priority: 70, getAttrs: link => linked(link) && picturable(linked(link).getAttribute('src')) && safeUrl(link.getAttribute('href')) ? { ...attributes(linked(link)), href: link.getAttribute('href') } : false },
            { tag: 'img[src]', getAttrs: image => picturable(image.getAttribute('src')) ? null : false },
        ],
        renderHTML: ({ node, HTMLAttributes }) => node.attrs.href ? ['a', { href: node.attrs.href }, ['img', HTMLAttributes]] : ['img', HTMLAttributes],
        // Images from elsewhere show while editing, whatever their host thinks of the page.
        addNodeView: () => resizable('img', ({ attrs }) => ({ src: attrs.src, alt: attrs.alt, title: attrs.title, width: attrs.width, height: attrs.height, referrerpolicy: 'no-referrer' })),
    })
}

// Images side by side: a grid of as many equal columns as it has images.
const rowStyle = count => `display: grid; grid-template-columns: repeat(${count}, minmax(0, 1fr)); gap: 8px; align-items: start;`

// An image as a row's column fills it; out of a row, it is as it was inserted, centered.
const inRow = image => image.type.create({ ...image.attrs, style: restyle(image.attrs.style, { 'width': '100%', 'margin-left': null, 'margin-right': null }) })
const outOfRow = image => image.type.create({ ...image.attrs, style: restyle(image.attrs.style, { 'width': null, 'margin-left': 'auto', 'margin-right': 'auto' }) })

// A grid row of images (an <img>, or a linked one, each), as the editor writes them.
const isRow = element => /grid/.test(element.style.display) && element.children.length > 1 && [...element.children].every(child => child.matches('img[src]') || (child.matches('a[href]') && child.children.length === 1 && child.firstElementChild.matches('img[src]')))

/**
 * Images side by side, in a row of equal columns: an image joins the next one (or the
 * one before, or their row, up to four) from its menu, and leaves its row the same way,
 * to stand under it; a row left with one image is that image again.
 */
export function imageRows() {
    return Node.create({
        name: 'imageRow',
        group: 'block',
        content: 'image+',
        draggable: true,
        parseHTML: () => [{ tag: 'div', priority: 60, getAttrs: element => isRow(element) ? null : false }],
        renderHTML: ({ node }) => ['div', { style: rowStyle(node.childCount) }, 0],
        // In the editor its images keep no handles: their column sizes them.
        addNodeView: () => ({ node }) => {
            const dom = document.createElement('div')

            dom.dataset.editorRow = ''
            dom.style.cssText = rowStyle(node.childCount)

            return {
                dom,
                contentDOM: dom,
                update: next => next.type === node.type && ((dom.style.cssText = rowStyle(next.childCount)), true),
            }
        },
        addCommands: () => ({
            toggleImageRow: () => ({ state, tr, dispatch }) => {
                const { selection, schema } = state
                const image = selection.node?.type.name === 'image' ? selection.node : null
                const row = schema.nodes.imageRow

                if (! image) return false

                const $position = state.doc.resolve(selection.from)
                const parent = $position.parent

                // Out of its row, under it; the row closes up.
                if (parent.type === row) {
                    if (dispatch) {
                        const others = parent.content.content.filter((child, index) => index !== $position.index())
                        const left = others.length === 1 ? [outOfRow(others[0])] : [row.create(null, others)]

                        tr.replaceWith($position.before(), $position.after(), [...left, outOfRow(image)])
                        tr.setSelection(NodeSelection.create(tr.doc, $position.before() + left[0].nodeSize))
                    }

                    return true
                }

                if (parent.type.name === 'figure') return false

                const fits = node => node?.type.name === 'image' || (node?.type === row && node.childCount < 4)
                const next = parent.maybeChild($position.index() + 1)
                const previous = $position.index() > 0 ? parent.maybeChild($position.index() - 1) : null
                const other = fits(next) ? next : fits(previous) ? previous : null

                if (! other) return false

                if (dispatch) {
                    const after = other === next
                    const images = other.type === row ? other.content.content : [inRow(other)]
                    const from = after ? selection.from : selection.from - other.nodeSize
                    const to = after ? selection.to + other.nodeSize : selection.to

                    tr.replaceWith(from, to, row.create(null, after ? [inRow(image), ...images] : [...images, inRow(image)]))
                    tr.setSelection(NodeSelection.create(tr.doc, from + 1 + (after ? 0 : images.reduce((size, node) => size + node.nodeSize, 0))))
                }

                return true
            },
        }),
        // A row that lost all but one image (deleted, moved out) is that image again.
        addProseMirrorPlugins: () => [new Plugin({
            appendTransaction: (transactions, previous, state) => {
                if (! transactions.some(transaction => transaction.docChanged)) return null

                const { tr } = state

                state.doc.descendants((node, position) => {
                    if (node.type.name !== 'imageRow') return

                    const mapped = tr.mapping.map(position)

                    if (node.childCount === 1) tr.replaceWith(mapped, mapped + node.nodeSize, outOfRow(node.firstChild))

                    return false
                })

                return tr.docChanged ? tr : null
            },
        })],
    })
}

/**
 * Captions: an image in a <figure> with its <figcaption> under it.
 */
export function figures(styles) {
    return [
        Node.create({
            name: 'figure',
            group: 'block',
            content: 'image figcaption',
            isolating: true,
            draggable: true,
            parseHTML: () => [{ tag: 'figure', priority: 60, getAttrs: figure => figure.querySelector('img[src]') ? null : false }],
            renderHTML: ({ HTMLAttributes }) => ['figure', mergeAttributes({ style: styles.figure }, HTMLAttributes), 0],
            addCommands: () => ({
                // A caption under the selected image, or the image alone again.
                toggleCaption: () => ({ state, tr, dispatch }) => {
                    const { selection, schema } = state
                    const image = selection.node?.type.name === 'image' ? selection.node : null

                    if (! image) return false

                    const $position = state.doc.resolve(selection.from)

                    // Writing goes on in the new caption; without one, the image stays selected.
                    if (dispatch && $position.parent.type.name === 'figure') {
                        tr.replaceWith($position.before(), $position.after(), image)
                        tr.setSelection(NodeSelection.create(tr.doc, $position.before()))
                    } else if (dispatch) {
                        tr.replaceWith(selection.from, selection.to, schema.nodes.figure.create(null, [image, schema.nodes.figcaption.create()]))
                        tr.setSelection(TextSelection.create(tr.doc, selection.from + image.nodeSize + 2))
                    }

                    return true
                },
            }),
            // A figure whose image went (replaced, deleted) leaves its caption as a paragraph.
            addProseMirrorPlugins: () => [new Plugin({
                appendTransaction: (transactions, previous, state) => {
                    if (! transactions.some(transaction => transaction.docChanged)) return null

                    const { tr } = state

                    state.doc.descendants((node, position) => {
                        if (node.type.name !== 'figure') return
                        if (! node.firstChild?.attrs.src) tr.replaceWith(tr.mapping.map(position), tr.mapping.map(position + node.nodeSize), state.schema.nodes.paragraph.create(null, node.lastChild.content))

                        return false
                    })

                    return tr.docChanged ? tr : null
                },
            })],
        }),
        Node.create({
            name: 'figcaption',
            content: 'inline*',
            // Only in a figure: elsewhere it is a block like any other.
            parseHTML: () => [{ tag: 'figcaption', context: 'figure/' }],
            renderHTML: ({ HTMLAttributes }) => ['figcaption', mergeAttributes({ style: styles.caption }, HTMLAttributes), 0],
        }),
    ]
}

const source = element => element.getAttribute('src') ?? element.querySelector('source[src]')?.getAttribute('src')

export const Video = Node.create({
    name: 'video',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes: () => ({
        src: { default: null, parseHTML: source },
        poster: { default: null },
        width: { default: null },
        height: { default: null },
        title: { default: null },
    }),

    parseHTML: () => [{ tag: 'video', getAttrs: video => source(video) && safeUrl(source(video)) ? null : false }],

    renderHTML: ({ HTMLAttributes }) => ['video', mergeAttributes(HTMLAttributes, { controls: 'controls', preload: 'metadata' })],

    addNodeView: () => resizable('video', ({ attrs }) => ({ src: attrs.src, poster: attrs.poster, title: attrs.title, width: attrs.width, height: attrs.height, controls: true, preload: 'metadata' })),
})

export const Audio = Node.create({
    name: 'audio',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes: () => ({
        src: { default: null, parseHTML: source },
        title: { default: null },
    }),

    parseHTML: () => [{ tag: 'audio', getAttrs: audio => source(audio) && safeUrl(source(audio)) ? null : false }],

    renderHTML: ({ HTMLAttributes }) => ['audio', mergeAttributes(HTMLAttributes, { controls: 'controls', preload: 'metadata' })],
})

/**
 * Files: a download link drawn as a card (clip, name, size) with inline style, so it
 * reads as one anywhere.
 */
export const file = card => Node.create({
    name: 'file',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes: () => ({
        href: own(null, link => link.getAttribute('href')),
        name: own(null, link => link.getAttribute('download') || link.querySelector(':scope > span:nth-of-type(2)')?.textContent || link.textContent.trim()),
        mime: own(null, link => link.getAttribute('type')),
        size: own(null, link => link.querySelector(':scope > span:nth-of-type(3)')?.textContent ?? null),
    }),

    parseHTML: () => [{ tag: 'a[download][href]', priority: 80, getAttrs: link => safeUrl(link.getAttribute('href')) ? null : false }],

    // Its parts styled (a long name wraps, the icon and size keep their size), never bare
    // spans, which filtering takes out.
    renderHTML: ({ node, HTMLAttributes }) => [
        'a',
        mergeAttributes({ href: node.attrs.href, download: node.attrs.name ?? '', type: node.attrs.mime, style: card }, HTMLAttributes),
        ['span', { style: 'flex-shrink:0' }, '📎'],
        ['span', { style: 'min-width:0px;overflow-wrap:anywhere' }, node.attrs.name ?? ''],
        ...node.attrs.size ? [['span', { style: 'flex-shrink:0;white-space:nowrap;opacity:0.6' }, node.attrs.size]] : [],
    ],
})

/**
 * Embeds: a platform's player in an iframe, 16:9 unless its style has another shape,
 * never playing by itself; other addresses don't load. While editing it takes no clicks until selected, so a
 * click selects it.
 */
export function embed(platforms) {
    return Node.create({
        name: 'embed',
        group: 'block',
        atom: true,
        draggable: true,

        // Never playing by itself, whatever its address said.
        addAttributes: () => ({
            src: { default: null, parseHTML: frame => still(secure(frame.getAttribute('src')), platforms) },
            title: { default: null },
        }),

        parseHTML: () => [{ tag: 'iframe[src]', getAttrs: frame => playable(secure(frame.getAttribute('src')), platforms) ? null : false }],

        // A 16:9 player as wide as the page, unless its style says otherwise.
        renderHTML: ({ HTMLAttributes }) => ['iframe', mergeAttributes({ style: EMBED_STYLE }, HTMLAttributes, { allowfullscreen: 'allowfullscreen', loading: 'lazy' })],

        addNodeView: () => resizable('iframe', ({ attrs }) => ({ src: attrs.src, title: attrs.title, allowfullscreen: true, loading: 'lazy' })),
    })
}

/**
 * Callouts: an <aside> of blocks, tinted and barred inline (see RichText::CALLOUTS).
 */
export function callout(styles) {
    return Node.create({
        name: 'callout',
        group: 'block',
        content: 'block+',
        defining: true,

        parseHTML: () => [{ tag: 'aside', priority: 60 }],

        renderHTML: ({ HTMLAttributes }) => ['aside', HTMLAttributes, 0],

        addCommands: () => ({
            // Wraps the selection's blocks in a callout of that look, changes the look of
            // the one it is in, or takes it off for none.
            setCallout: look => ({ editor, commands }) => {
                const style = look ? `${styles.callout};${look}` : null

                if (editor.isActive('callout')) return style ? commands.updateAttributes('callout', { style }) : commands.lift('callout')

                return !! style && commands.wrapIn('callout', { style })
            },
        }),
    })
}

// A formula's MathML as an element, or nothing to show yet.
function mathElement(mathml) {
    const template = document.createElement('template')

    template.innerHTML = mathml ?? ''

    return template.content.querySelector('math')
}

/**
 * Formulas: MathML, which browsers show natively, carrying its LaTeX in an annotation
 * to edit it again. KaTeX writes the MathML, loaded when a formula is first edited.
 */
export function formulas() {
    const formula = (name, inline) => Node.create({
        name,
        group: inline ? 'inline' : 'block',
        inline,
        atom: true,
        draggable: ! inline,

        addAttributes: () => ({
            latex: { default: '', rendered: false, parseHTML: math => math.querySelector('annotation[encoding="application/x-tex"]')?.textContent ?? '' },
            mathml: { default: null, rendered: false, parseHTML: math => math.outerHTML },
        }),

        parseHTML: () => [{ tag: inline ? 'math' : 'math[display="block"]', priority: inline ? 50 : 60 }],

        renderHTML: ({ node }) => mathElement(node.attrs.mathml) ?? ['math', inline ? {} : { display: 'block' }],

        addNodeView: () => ({ node }) => {
            const dom = document.createElement(inline ? 'span' : 'div')

            dom.dataset.editorMath = inline ? 'inline' : 'block'
            dom.append(mathElement(node.attrs.mathml) ?? '')

            return { dom }
        },
    })

    return [formula('inlineMath', true), formula('blockMath', false)]
}

/**
 * MathML for LaTeX, with its source annotated; throws on LaTeX KaTeX can't read.
 */
export async function toMathML(latex, display) {
    const { default: katex } = await import('katex')
    const html = katex.renderToString(latex, { output: 'mathml', displayMode: display, throwOnError: true })

    return mathElement(html).outerHTML
}
