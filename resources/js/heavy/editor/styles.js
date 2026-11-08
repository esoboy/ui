/*
 * The Editor's styles: every node and mark keeps its style as written (the whitelist
 * decides which properties), and the controls change one property of it at a time,
 * with the values they offer: palette colors and highlights, fonts, sizes, alignment,
 * indents, first-line indents, line heights, paragraph and letter spacing.
 */

import { Extension, Mark } from '@tiptap/core'
import Highlight from '@tiptap/extension-highlight'

const BLOCKS = ['paragraph', 'heading']

// The block styles clearing takes off (marks go too).
const BLOCK_PROPERTIES = ['text-align', 'margin-left', 'text-indent', 'line-height', 'margin-top', 'margin-bottom', 'letter-spacing']

let scratch = null

const declaration = () => (scratch ??= document.createElement('div')).style

/**
 * A style with properties set (or removed, for no value), as the browser writes it: a
 * shorthand set before is split rather than left to override the change.
 */
export function restyle(style, changes) {
    const css = declaration()

    css.cssText = style ?? ''

    for (const [property, value] of Object.entries(changes)) {
        if (value == null || value === '') css.removeProperty(property)
        else css.setProperty(property, value)
    }

    return css.cssText || null
}

// A property's value in a style, shorthands read too.
export function computed(style, property) {
    const css = declaration()

    css.cssText = style ?? ''

    return css.getPropertyValue(property) || null
}

let canvas = null

// [r, g, b] of #rgb, #rrggbb, rgb(), rgba() or a color's name, or null.
export function rgb(value) {
    value = String(value ?? '').trim().toLowerCase()

    if (/^[a-z]+$/.test(value) && value !== 'transparent') {
        canvas ??= document.createElement('canvas').getContext?.('2d') ?? null
        if (canvas) {
            canvas.fillStyle = '#010203'
            canvas.fillStyle = value
            if (canvas.fillStyle !== '#010203') value = canvas.fillStyle
        }
    }

    const hex = value.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/)?.[1]

    if (hex) return (hex.length === 3 ? [...hex].map(digit => digit + digit) : hex.match(/../g)).map(pair => parseInt(pair, 16))

    const channels = value.match(/^rgba?\(\s*(\d+)[\s,]+(\d+)[\s,]+(\d+)(?:\s*[,/]\s*([\d.]+)(%?))?\s*\)$/)

    return channels ? channels.slice(1, 4).map(Number) : null
}

/**
 * Whether two CSS values are the same: colors by their channels, the rest as written.
 */
export function same(a, b) {
    if (a == null || b == null || a === '' || b === '') return (a ?? '') === (b ?? '')

    const [x, y] = [rgb(a), rgb(b)]

    if (x && y) return x.every((channel, index) => channel === y[index])

    return String(a).replace(/\s+/g, '').toLowerCase() === String(b).replace(/\s+/g, '').toLowerCase()
}

// The style of the selection's block (a paragraph or a heading).
export function blockStyle(editor) {
    return editor.getAttributes('paragraph').style ?? editor.getAttributes('heading').style ?? null
}

// Changes the style of every paragraph and heading the selection touches; possible
// wherever there is one.
function restyleBlocks(change) {
    return ({ tr, state, dispatch }) => {
        let found = false

        state.doc.nodesBetween(state.selection.from, state.selection.to, (node, position) => {
            if (! BLOCKS.includes(node.type.name)) return

            found = true

            if (dispatch) {
                const style = change(node.attrs.style)

                if ((style ?? null) !== (node.attrs.style ?? null)) tr.setNodeMarkup(position, undefined, { ...node.attrs, style })
            }

            return false
        })

        return found
    }
}

/**
 * Sets a property of the selection's text style (span), keeping each run's others;
 * with nothing selected, of the text typed next.
 */
function restyleText(property, value) {
    return ({ tr, state, dispatch }) => {
        const type = state.schema.marks.textStyle

        if (! type) return false

        const { from, to, empty, $from } = state.selection
        const mark = current => {
            const style = restyle(current?.attrs.style, { [property]: value })

            return style || current?.attrs.extras ? type.create({ ...current?.attrs, style }) : null
        }

        if (! dispatch) return true

        if (empty) {
            const marks = state.storedMarks ?? $from.marks()
            const next = mark(marks.find(candidate => candidate.type === type))

            tr.setStoredMarks([...marks.filter(candidate => candidate.type !== type), ...next ? [next] : []])

            return true
        }

        state.doc.nodesBetween(from, to, (node, position) => {
            if (! node.isText) return

            const start = Math.max(position, from)
            const end = Math.min(position + node.nodeSize, to)
            const next = mark(node.marks.find(candidate => candidate.type === type))

            next ? tr.addMark(start, end, next) : tr.removeMark(start, end, type)
        })

        return true
    }
}

const level = style => {
    const margin = computed(style, 'margin-left') ?? ''
    const number = parseFloat(margin)

    return number > 0 ? Math.round(margin.endsWith('em') ? number / 2 : number / 32) : 0
}

export function styleExtensions(format, list) {
    const has = feature => format.features.includes(feature)
    const extensions = []

    if (list.allows('span')) {
        // A span is kept for what the whitelist keeps of it.
        extensions.push(Mark.create({
            name: 'textStyle',
            priority: 101,
            parseHTML: () => [{ tag: 'span', consuming: false, getAttrs: element => list.style(element) || list.extras(element) ? null : false }],
            renderHTML: ({ HTMLAttributes }) => ['span', HTMLAttributes, 0],
            addCommands: () => ({ setTextStyle: (property, value) => restyleText(property, value) }),
        }))
    }

    if (has('highlight')) {
        // Outside text colors, so a colored word stays its color on the highlight; dark
        // text on it, so it reads on dark pages too.
        extensions.push(Highlight.extend({
            priority: 102,
            addAttributes: () => ({}),
            parseHTML: () => [{ tag: 'mark' }],
            renderHTML: ({ HTMLAttributes }) => ['mark', HTMLAttributes, 0],
            addCommands() {
                return {
                    ...this.parent?.(),
                    setHighlightColor: color => ({ commands }) => commands.setMark(this.name, { style: `background-color: ${color}; color: ${format.highlightText}` }),
                }
            },
            addKeyboardShortcuts() {
                return { 'Mod-Shift-h': () => this.editor.isActive(this.name) ? this.editor.commands.unsetHighlight() : this.editor.commands.setHighlightColor(format.highlights[0]) }
            },
        }))
    }

    extensions.push(Extension.create({
        name: 'blockStyles',

        addCommands: () => ({
            setBlockStyle: (property, value) => restyleBlocks(style => restyle(style, { [property]: value })),
            // In a list, a level of the list; else 2em more of margin.
            indent: () => props => {
                const item = ['taskItem', 'listItem'].find(name => props.editor.isActive(name))

                if (item) return props.commands.sinkListItem(item)

                return has('indent') && restyleBlocks(style => restyle(style, { 'margin-left': `${Math.min(format.indents, level(style) + 1) * 2}em` }))(props)
            },
            outdent: () => props => {
                const item = ['taskItem', 'listItem'].find(name => props.editor.isActive(name))

                if (item) return props.commands.liftListItem(item)

                return has('indent') && restyleBlocks(style => restyle(style, { 'margin-left': level(style) > 1 ? `${(level(style) - 1) * 2}em` : null }))(props)
            },
            toggleTextIndent: () => props => {
                const indented = !! computed(blockStyle(props.editor), 'text-indent')

                return restyleBlocks(style => restyle(style, { 'text-indent': indented ? null : '2em' }))(props)
            },
            clearFormatting: () => ({ chain }) => chain()
                .unsetAllMarks()
                .command(restyleBlocks(style => restyle(style, Object.fromEntries(BLOCK_PROPERTIES.map(property => [property, null])))))
                .run(),
        }),

        addKeyboardShortcuts() {
            return {
                'Mod-]': () => this.editor.commands.indent(),
                'Mod-[': () => this.editor.commands.outdent(),
            }
        },
    }))

    return extensions
}
