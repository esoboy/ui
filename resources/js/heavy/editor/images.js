/*
 * Images not saved to the app yet: on another host than the page's and the app's own
 * (config ui.editor.hosts, data-image-hosts), or data: (pasted from a page, a
 * screenshot tool or Word). They stay as they are until saved, from the image's menu or
 * the footer's "save all", or as they come in (ui.editor.auto_upload): each goes to
 * the app's uploader as a file (upload.js), and its copy takes their place wherever
 * they are by then. Meanwhile they show as saving.
 */

import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'

export const savingKey = new PluginKey('editorSaving')

const DATA = /^data:image\/(?:png|jpe?g|gif|webp|bmp|avif);base64,[a-z\d+/]+={0,2}$/i

// Pictures, as the uploader takes images (EditorUploads::IMAGES): not SVG, which holds markup.
const PICTURES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/bmp', 'image/avif']

// A picture written into the address: what the editor keeps of data: images (not SVG,
// which holds markup).
export const dataImage = src => DATA.test(String(src ?? '').trim())

// Whether a host is one of these: itself, or under *.example.com.
export const ownHost = (host, hosts) => hosts.some(own => own === host || (own.startsWith('*.') && host.endsWith(own.slice(1))))

// Whether an image address is on another host than the page or the app's own.
export function external(src, hosts) {
    try {
        const url = new URL(src, document.baseURI)

        return ['http:', 'https:'].includes(url.protocol) && url.host !== location.host && ! ownHost(url.host, hosts)
    } catch {
        return false
    }
}

// external, data, or null for an image the app has.
export function unsaved(src, hosts) {
    if (String(src ?? '').startsWith('data:')) return 'data'

    return src && external(src, hosts) ? 'external' : null
}

// The unsaved images' addresses in a document, each once.
export function unsavedImages(doc, hosts) {
    const found = new Set()

    doc.descendants(node => {
        if (node.type.name === 'image' && unsaved(node.attrs.src, hosts)) found.add(node.attrs.src)
    })

    return [...found]
}

// A data: image as a file to upload, named by its place among those asked for.
export function dataFile(src, index = 0) {
    const [, type, data] = src.match(/^data:([^;,]+);base64,(.*)$/i)
    const bytes = Uint8Array.from(atob(data), character => character.charCodeAt(0))

    return new File([bytes], `image-${index + 1}.${type.split('/')[1].replace('jpeg', 'jpg')}`, { type })
}

/**
 * Word's pictures, from the RTF it copies alongside its HTML (whose images are files on
 * the computer, which a page can't read): PNG and JPEG ones, as data: images, in order.
 */
export function rtfImages(rtf) {
    const pictures = []

    // {\pict, its control words (and groups), then the picture in hex up to its }.
    for (const [, words, hex] of String(rtf ?? '').matchAll(/\{\\pict(?![a-z])([\s\S]*?)[\s}]((?:[\da-f]{2}\s*){64,})\}/gi)) {
        const type = /\\pngblip/.test(words) ? 'image/png' : /\\jpegblip/.test(words) ? 'image/jpeg' : null
        const digits = hex.replace(/\s+/g, '')
        let binary = ''

        if (! type) continue

        for (let index = 0; index < digits.length; index += 2) binary += String.fromCharCode(parseInt(digits.slice(index, index + 2), 16))

        pictures.push(`data:${type};base64,${btoa(binary)}`)
    }

    return pictures
}

// Where a picture that couldn't be read was: a mark the paste leaves for a placeholder
// (uploads.js), never kept in the text.
export const MISSING = '￼'

/**
 * HTML pasted from Word with its pictures put back: its file:// images take the RTF's
 * pictures, in order, when there are as many of each. Otherwise (a chart Word drew has
 * none, WPS may copy no RTF) each is marked MISSING, for a placeholder to stand there.
 */
export function wordImages(html, rtf) {
    if (! /<img[^>]+src=["']?file:/i.test(html)) return html

    const template = document.createElement('template')

    template.innerHTML = html

    const images = [...template.content.querySelectorAll('img')].filter(image => /^file:/i.test(image.getAttribute('src') ?? ''))
    const pictures = rtf ? rtfImages(rtf) : []

    if (images.length === pictures.length) images.forEach((image, index) => image.setAttribute('src', pictures[index]))
    else images.forEach(image => image.replaceWith(MISSING))

    return template.innerHTML
}

/**
 * Another site's image as a file, as the browser reads it: only when its site lets pages
 * read it (CORS), and a picture; otherwise it throws, for the server to fetch it.
 */
export async function imageFile(src) {
    const response = await fetch(src, { mode: 'cors', credentials: 'omit', referrerPolicy: 'no-referrer' })

    if (! response.ok) throw new Error(`The image answered ${response.status}.`)

    const blob = await response.blob()
    const type = blob.type.split(';')[0].trim().toLowerCase()

    if (! PICTURES.includes(type)) throw new Error('The address is not a picture.')

    const name = decodeURIComponent(new URL(src, document.baseURI).pathname.split('/').pop() ?? '').replace(/\.[^.]*$/, '') || 'image'

    return new File([blob], `${name}.${type.split('/')[1].replace('jpeg', 'jpg')}`, { type })
}

function decorate(doc, pending) {
    const decorations = []

    if (pending.size) {
        doc.descendants((node, position) => {
            if (node.type.name === 'image' && pending.has(node.attrs.src)) decorations.push(Decoration.node(position, position + node.nodeSize, { 'data-editor-saving': '' }))
        })
    }

    return DecorationSet.create(doc, decorations)
}

export const Saving = Extension.create({
    name: 'saving',

    addCommands: () => ({
        // The images being saved, by address.
        setSaving: pending => ({ tr, dispatch }) => {
            if (dispatch) tr.setMeta(savingKey, new Set(pending))

            return true
        },
    }),

    addProseMirrorPlugins: () => [
        new Plugin({
            key: savingKey,
            state: {
                init: () => ({ pending: new Set(), decorations: DecorationSet.empty }),
                apply(tr, previous) {
                    const pending = tr.getMeta(savingKey) ?? previous.pending

                    if (pending === previous.pending && ! tr.docChanged) return previous

                    return { pending, decorations: decorate(tr.doc, pending) }
                },
            },
            props: {
                decorations: state => savingKey.getState(state).decorations,
            },
        }),
    ],
})
