/*
 * What [data-editor-command] buttons and menu items do: run(chain, value, editor) builds
 * the command, active(editor, value) says whether it is on at the selection. A value
 * comes from data-editor-value (a heading's level, a color, a size, …); values compare
 * as CSS values, so a color written rgb() is the palette's hex.
 */

import { CellSelection } from '@tiptap/pm/tables'
import { EMBED_STYLE } from './media.js'
import { alignOf } from './resize.js'
import { blockStyle, computed, restyle, same } from './styles.js'

const toggle = (command, name, attributes) => ({ run: chain => chain[command](), active: editor => editor.isActive(name, attributes) })

// A property of the text style (span); '' unsets it.
const textStyle = property => ({
    run: (chain, value) => chain.setTextStyle(property, value || null),
    active: (editor, value) => same(computed(editor.getAttributes('textStyle').style, property), value),
})

// A property of the selection's paragraphs and headings; '' unsets it.
const block = (property, fallback = '') => ({
    run: (chain, value) => chain.setBlockStyle(property, value || null),
    active: (editor, value) => same(computed(blockStyle(editor), property) ?? fallback, value || fallback),
})

const selected = editor => editor.state.selection.node ?? null

// The selected image, video or embed's style, changed.
const media = (change, active) => ({
    run: (chain, value, editor) => {
        const node = selected(editor)

        return node && 'style' in node.attrs ? chain.updateAttributes(node.type.name, { style: change(node, value) }) : chain.command(() => false)
    },
    active: (editor, value) => !! selected(editor) && active(selected(editor), value),
})

const MARGINS = { left: { 'margin-left': null, 'margin-right': null }, center: { 'margin-left': 'auto', 'margin-right': 'auto' }, right: { 'margin-left': 'auto', 'margin-right': null } }

// The cells of the selection, or the one the cursor is in, as [cell, position].
function cells(state) {
    if (state.selection instanceof CellSelection) {
        const found = []

        state.selection.forEachCell((cell, position) => found.push([cell, position]))

        return found
    }

    const { $from } = state.selection

    for (let depth = $from.depth; depth > 0; depth--) {
        if (['tableCell', 'tableHeader'].includes($from.node(depth).type.name)) return [[$from.node(depth), $from.before(depth)]]
    }

    return []
}

// A property of the selected cells' style.
const cell = property => ({
    run: (chain, value) => chain.command(({ tr, state, dispatch }) => {
        const found = cells(state)

        if (dispatch) found.forEach(([node, position]) => tr.setNodeMarkup(position, undefined, { ...node.attrs, style: restyle(node.attrs.style, { [property]: value || null }) }))

        return found.length > 0
    }),
    active: (editor, value) => {
        const [first] = cells(editor.state)

        return !! first && same(computed(first[0].attrs.style, property) ?? '', value)
    },
})

export const COMMANDS = {
    'undo': { run: chain => chain.undo() },
    'redo': { run: chain => chain.redo() },

    'paragraph': { run: chain => chain.setParagraph(), active: editor => editor.isActive('paragraph') },
    'heading': { run: (chain, level) => chain.toggleHeading({ level: Number(level) }), active: (editor, level) => editor.isActive('heading', { level: Number(level) }) },

    'bold': toggle('toggleBold', 'bold'),
    'italic': toggle('toggleItalic', 'italic'),
    'underline': toggle('toggleUnderline', 'underline'),
    'strike': toggle('toggleStrike', 'strike'),
    'code': toggle('toggleCode', 'code'),
    'superscript': toggle('toggleSuperscript', 'superscript'),
    'subscript': toggle('toggleSubscript', 'subscript'),

    'color': textStyle('color'),
    'font-family': textStyle('font-family'),
    'font-size': textStyle('font-size'),
    'highlight': {
        run: (chain, color) => color ? chain.setHighlightColor(color) : chain.unsetHighlight(),
        active: (editor, color) => color ? same(computed(editor.getAttributes('highlight').style, 'background-color'), color) : ! editor.isActive('highlight'),
    },
    'clear-formatting': { run: chain => chain.clearFormatting() },

    'text-align': block('text-align', 'left'),
    'line-height': block('line-height'),
    'margin-top': block('margin-top'),
    'margin-bottom': block('margin-bottom'),
    'letter-spacing': block('letter-spacing'),
    'indent': { run: chain => chain.indent() },
    'outdent': { run: chain => chain.outdent() },
    'text-indent': { run: chain => chain.toggleTextIndent(), active: editor => !! computed(blockStyle(editor), 'text-indent') },

    'bullet-list': toggle('toggleBulletList', 'bulletList'),
    'ordered-list': toggle('toggleOrderedList', 'orderedList'),
    'task-list': toggle('toggleTaskList', 'taskList'),
    'blockquote': toggle('toggleBlockquote', 'blockquote'),
    'code-block': toggle('toggleCodeBlock', 'codeBlock'),
    'horizontal-rule': { run: chain => chain.setHorizontalRule() },
    'details': { run: chain => chain.setDetails(), active: editor => editor.isActive('details') },
    'callout': {
        run: (chain, look) => chain.setCallout(look || null),
        active: (editor, look) => look ? editor.isActive('callout') && same(computed(editor.getAttributes('callout').style, 'border-left-color'), computed(look, 'border-left-color')) : ! editor.isActive('callout'),
    },

    'table': { run: chain => chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }) },
    'add-row-before': { run: chain => chain.addRowBefore() },
    'add-row-after': { run: chain => chain.addRowAfter() },
    'delete-row': { run: chain => chain.deleteRow() },
    'add-column-before': { run: chain => chain.addColumnBefore() },
    'add-column-after': { run: chain => chain.addColumnAfter() },
    'delete-column': { run: chain => chain.deleteColumn() },
    'merge-cells': { run: chain => chain.mergeCells() },
    'split-cell': { run: chain => chain.splitCell() },
    'header-row': { run: chain => chain.toggleHeaderRow() },
    'header-column': { run: chain => chain.toggleHeaderColumn() },
    'delete-table': { run: chain => chain.deleteTable() },
    'cell-background': cell('background-color'),
    'cell-align': cell('vertical-align'),

    // Width: '' for its own (an embed's own is the page's); alignment by its margins.
    'media-size': media(
        (node, size) => restyle(node.attrs.style ?? (node.type.name === 'embed' ? EMBED_STYLE : null), { width: size || (node.type.name === 'embed' ? '100%' : null) }),
        (node, size) => same(computed(node.attrs.style, 'width') ?? (node.type.name === 'embed' ? '100%' : ''), size || (node.type.name === 'embed' ? '100%' : '')),
    ),
    'media-align': media(
        (node, align) => restyle(node.attrs.style, MARGINS[align] ?? MARGINS.left),
        (node, align) => alignOf(node.attrs.style) === align,
    ),
    // An embed's shape: its aspect ratio, 16:9 by default.
    'embed-ratio': media(
        (node, ratio) => node.type.name === 'embed' ? restyle(node.attrs.style ?? EMBED_STYLE, { 'aspect-ratio': ratio || null }) : node.attrs.style,
        (node, ratio) => node.type.name === 'embed' && same(computed(node.attrs.style ?? EMBED_STYLE, 'aspect-ratio'), ratio),
    ),
    // Side by side with the next image (or the one before), or out of its row.
    'image-row': {
        run: chain => chain.toggleImageRow(),
        active: editor => selected(editor)?.type.name === 'image' && editor.state.doc.resolve(editor.state.selection.from).parent.type.name === 'imageRow',
    },
    'caption': {
        run: chain => chain.toggleCaption(),
        active: editor => selected(editor)?.type.name === 'image' && editor.state.doc.resolve(editor.state.selection.from).parent.type.name === 'figure',
    },
    'delete-node': { run: chain => chain.deleteSelection() },
}
