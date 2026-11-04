/*
 * Find and replace: the matches of a search, marked in the text, one of them current;
 * replacing keeps the marks of the text it replaces.
 */

import { Extension } from '@tiptap/core'
import { Plugin, PluginKey, TextSelection } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'

export const findKey = new PluginKey('editorFind')

// Every match in the document's text blocks, as { from, to }.
function search(doc, query, caseSensitive) {
    const matches = []

    if (! query) return matches

    const needle = caseSensitive ? query : query.toLocaleLowerCase()

    doc.descendants((node, position) => {
        if (! node.isTextblock) return true

        // One character per inline node, so offsets are positions within the block.
        let text = node.textBetween(0, node.content.size, undefined, '￼')

        if (! caseSensitive) text = text.toLocaleLowerCase()

        for (let index = text.indexOf(needle); index !== -1; index = text.indexOf(needle, index + needle.length)) {
            matches.push({ from: position + 1 + index, to: position + 1 + index + needle.length })
        }

        return false
    })

    return matches
}

const empty = { query: '', caseSensitive: false, matches: [], current: 0 }

function decorations(doc, { matches, current }) {
    return DecorationSet.create(doc, matches.map(({ from, to }, index) => Decoration.inline(from, to, { 'data-editor-match': index === current ? 'current' : '' })))
}

export const Find = Extension.create({
    name: 'find',

    addCommands: () => ({
        find: (query, caseSensitive = false) => ({ tr, dispatch }) => {
            if (dispatch) tr.setMeta(findKey, { query, caseSensitive })

            return true
        },
        // Moves to the next match (back with -1) and selects it.
        findNext: (step = 1) => ({ state, tr, dispatch }) => {
            const { matches, current } = findKey.getState(state)

            if (! matches.length) return false

            const next = (current + step + matches.length) % matches.length

            if (dispatch) tr.setMeta(findKey, { current: next }).setSelection(TextSelection.create(tr.doc, matches[next].from, matches[next].to)).scrollIntoView()

            return true
        },
        replace: replacement => ({ state, tr, dispatch }) => {
            const { matches, current } = findKey.getState(state)
            const match = matches[current]

            if (! match) return false
            if (dispatch) tr.insertText(replacement, match.from, match.to)

            return true
        },
        replaceAll: replacement => ({ state, tr, dispatch }) => {
            const { matches } = findKey.getState(state)

            if (! matches.length) return false
            if (dispatch) [...matches].reverse().forEach(({ from, to }) => tr.insertText(replacement, from, to))

            return true
        },
    }),

    addProseMirrorPlugins: () => [
        new Plugin({
            key: findKey,
            state: {
                init: () => ({ ...empty, decorations: DecorationSet.empty }),
                apply(tr, previous) {
                    const meta = tr.getMeta(findKey)

                    if (! meta && ! tr.docChanged) return previous

                    const next = { ...previous, ...meta }

                    if (tr.docChanged || 'query' in (meta ?? {}) || 'caseSensitive' in (meta ?? {})) {
                        next.matches = search(tr.doc, next.query, next.caseSensitive)
                        // After a search or a replacement, the first match from where the current one was.
                        const from = previous.matches[previous.current]?.from ?? 0
                        const after = next.matches.findIndex(match => match.from >= tr.mapping.map(from))

                        next.current = meta && 'current' in meta ? meta.current : Math.max(0, after)
                    }

                    next.current = Math.min(next.current, Math.max(0, next.matches.length - 1))
                    next.decorations = decorations(tr.doc, next)

                    return next
                },
            },
            props: {
                decorations: state => findKey.getState(state).decorations,
            },
        }),
    ],
})
