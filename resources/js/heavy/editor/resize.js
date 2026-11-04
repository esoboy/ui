/*
 * Images, videos and embeds while editing: the media in a box as wide as its style's
 * width and placed by its margins, the rest of its style (a radius, a border, …) on
 * the media itself. Handles on the sides it can grow from (both when centered) drag
 * its width, in px, or 100% at the full width.
 */

import { computed, restyle } from './styles.js'

const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' }

// left, center or right, as its margins place a block.
export function alignOf(style) {
    if (computed(style, 'margin-left') !== 'auto') return 'left'

    return computed(style, 'margin-right') === 'auto' ? 'center' : 'right'
}

/**
 * A node view for a media node: tag is its element, attributes(node) what that element
 * carries besides its style.
 */
export function resizable(tag, attributes) {
    return ({ node, getPos, editor }) => {
        const dom = document.createElement('div')
        const box = document.createElement('div')
        const media = document.createElement(tag)
        const handles = ['left', 'right'].map(side => {
            const handle = document.createElement('span')

            handle.dataset.editorResize = side
            handle.setAttribute('aria-hidden', 'true')

            return handle
        })

        dom.dataset.editorMedia = node.type.name
        box.dataset.editorMediaBox = ''
        media.draggable = false
        box.append(media, ...handles)
        dom.append(box)

        const render = () => {
            const style = node.attrs.style
            const width = computed(style, 'width') ?? (tag === 'iframe' ? '100%' : null)
            const align = alignOf(style)

            dom.style.justifyContent = JUSTIFY[align]
            dom.dataset.align = align
            box.style.width = width ?? ''

            for (const [name, value] of Object.entries(attributes(node))) {
                if (value == null || value === false) media.removeAttribute(name)
                else if (media.getAttribute(name) !== String(value)) media.setAttribute(name, value === true ? '' : value)
            }

            // The box places and sizes it; the media keeps the look of its style.
            media.style.cssText = restyle(style, { 'margin': null, 'width': width ? '100%' : null, 'max-width': '100%', 'height': tag === 'iframe' ? 'auto' : computed(style, 'height') ?? 'auto', 'display': 'block' }) ?? ''

            if (tag === 'iframe') {
                media.style.aspectRatio ||= '16 / 9'
                media.style.border ||= '0'
            }
        }

        const start = event => {
            if (! editor.isEditable || event.button > 0) return

            const handle = event.currentTarget
            const from = event.clientX
            const width = box.getBoundingClientRect().width
            const max = dom.clientWidth
            const grows = (handle.dataset.editorResize === 'left' ? -1 : 1) * (dom.dataset.align === 'center' ? 2 : 1)

            event.preventDefault()
            event.stopPropagation()
            handle.setPointerCapture?.(event.pointerId)
            dom.dataset.resizing = ''

            const move = moved => {
                box.style.width = `${Math.round(Math.min(max, Math.max(48, width + grows * (moved.clientX - from))))}px`
            }

            const end = () => {
                handle.removeEventListener('pointermove', move)
                handle.removeEventListener('pointerup', end)
                handle.removeEventListener('pointercancel', end)
                delete dom.dataset.resizing

                const position = getPos()
                const size = parseFloat(box.style.width) >= max ? '100%' : box.style.width
                const style = restyle(node.attrs.style, { width: size })

                if (typeof position !== 'number' || style === node.attrs.style) return render()

                editor.view.dispatch(editor.state.tr.setNodeMarkup(position, undefined, { ...node.attrs, style }))
            }

            handle.addEventListener('pointermove', move)
            handle.addEventListener('pointerup', end)
            handle.addEventListener('pointercancel', end)
        }

        handles.forEach(handle => handle.addEventListener('pointerdown', start))
        render()

        return {
            dom,
            update(next) {
                if (next.type !== node.type) return false

                node = next
                if (! ('resizing' in dom.dataset)) render()

                return true
            },
            // The handles are the view's own; ProseMirror leaves their events and its changes be.
            stopEvent: event => !! event.target.closest?.('[data-editor-resize]'),
            ignoreMutation: mutation => mutation.type !== 'selection',
        }
    }
}
