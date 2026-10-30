/*
 * Placeholders, never part of the content: while what was picked, dropped or pasted
 * uploads, one (the view's uploading template) stands where it will go, until it is
 * there or dismissed; where it wouldn't upload, one (failed) says why, to dismiss;
 * where a pasted picture couldn't be read (images.js MISSING), one (missing) says so,
 * to upload it there (saying why, if that fails) or dismiss.
 */

import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'
import { MISSING } from './images.js'

export const uploadsKey = new PluginKey('editorUploads')

let missing = 0

export function uploads(templates) {
    return Extension.create({
        name: 'uploads',

        addCommands: () => ({
            addUpload: (id, position, kind = 'uploading', message = null) => ({ tr, dispatch }) => {
                if (dispatch) tr.setMeta(uploadsKey, { add: [{ id, position, kind, message }] })

                return true
            },
            removeUpload: id => ({ tr, dispatch }) => {
                if (dispatch) tr.setMeta(uploadsKey, { remove: id })

                return true
            },
        }),

        addProseMirrorPlugins: () => [
            new Plugin({
                key: uploadsKey,
                state: {
                    init: () => DecorationSet.empty,
                    apply(tr, set) {
                        const meta = tr.getMeta(uploadsKey)

                        set = set.map(tr.mapping, tr.doc)

                        if (meta?.remove) set = set.remove(set.find(undefined, undefined, spec => spec.id === meta.remove))

                        for (const { id, position, kind, message } of meta?.add ?? []) {
                            const template = templates[kind]
                            const widget = () => {
                                const element = template.content.firstElementChild.cloneNode(true)
                                const text = element.querySelector('[data-editor-upload-message]')

                                element.dataset.editorUpload = id
                                element.querySelectorAll('[data-editor-action]').forEach(control => control.setAttribute('data-editor-value', id))
                                if (text && message) {
                                    text.textContent = message
                                    text.hidden = false
                                }

                                return element
                            }

                            if (template) set = set.add(tr.doc, [Decoration.widget(Math.min(position, tr.doc.content.size), widget, { id, kind, message, side: -1 })])
                        }

                        return set
                    },
                },
                props: {
                    decorations: state => uploadsKey.getState(state),
                },
                // What a paste or drop marked missing: the mark goes, a placeholder stands there.
                appendTransaction(transactions, previous, state) {
                    if (! templates.missing || ! transactions.some(tr => tr.docChanged && (tr.getMeta('paste') || ['paste', 'drop'].includes(tr.getMeta('uiEvent'))))) return null

                    const marks = []

                    state.doc.descendants((node, position) => {
                        if (! node.isText) return

                        for (let index = node.text.indexOf(MISSING); index >= 0; index = node.text.indexOf(MISSING, index + 1)) marks.push(position + index)
                    })

                    if (! marks.length) return null

                    const { tr } = state

                    marks.reverse().forEach(position => tr.delete(position, position + 1))

                    return tr.setMeta(uploadsKey, { add: marks.map(position => ({ id: `missing-${++missing}`, position: tr.mapping.map(position), kind: 'missing' })) })
                },
            }),
        ],
    })
}
