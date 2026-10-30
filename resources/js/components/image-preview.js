/*
 * <eso-image-preview>
 *
 * A click on a thumbnail opens the viewer at its image; the arrows page through the
 * group's other thumbnails. The image zooms (buttons, wheel, + and -, double click),
 * rotates, and pans by dragging once zoomed; 0 resets it. Esc or a click beside the
 * image closes the viewer.
 */

import { connected, define, disconnected } from '../support/element.js'

const MIN = 0.5
const MAX = 5
const FACTOR = 1.5

export class ImagePreviewElement extends HTMLElement {
    #index = 0
    #scale = 1
    #rotate = 0
    #x = 0
    #y = 0
    #drag = null
    #dragged = false

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('click', event => {
            const trigger = event.target.closest?.('[data-slot="image-preview-trigger"]')

            if (trigger) return this.open(this.#triggers().indexOf(trigger))

            const action = event.target.closest?.('[data-image-preview]')

            if (action && this.dialog.contains(action)) return this.#act(action.dataset.imagePreview)

            // A click beside the image, but not the end of a drag.
            if (event.target === this.dialog && ! this.#dragged) this.dialog.close()
            this.#dragged = false
        }, { signal })

        this.addEventListener('keydown', event => {
            if (! this.dialog?.contains(event.target)) return

            const action = { ArrowLeft: 'previous', ArrowRight: 'next', '+': 'zoom-in', '=': 'zoom-in', '-': 'zoom-out', 0: 'reset' }[event.key]

            if (action) {
                event.preventDefault()
                this.#act(action)
            }
        }, { signal })

        this.addEventListener('wheel', event => {
            if (! this.dialog?.open || ! this.dialog.contains(event.target)) return

            event.preventDefault()
            this.#zoom(this.#scale * Math.exp(-event.deltaY * 0.002))
        }, { passive: false, signal })

        this.addEventListener('dblclick', event => {
            if (event.target === this.image) this.#scale > 1 ? this.#reset() : this.#zoom(2)
        }, { signal })

        this.addEventListener('pointerdown', event => {
            if (event.target !== this.image || this.#scale <= 1 || event.button !== 0) return

            event.preventDefault()
            this.image.setPointerCapture?.(event.pointerId)
            this.#drag = { pointer: event.pointerId, x: event.clientX - this.#x, y: event.clientY - this.#y }
            this.image.toggleAttribute('data-dragging', true)
        }, { signal })

        this.addEventListener('pointermove', event => {
            if (! this.#drag || this.#drag.pointer !== event.pointerId) return

            this.#x = event.clientX - this.#drag.x
            this.#y = event.clientY - this.#drag.y
            this.#dragged = true
            this.#apply()
        }, { signal })

        for (const type of ['pointerup', 'pointercancel']) {
            this.addEventListener(type, () => {
                this.#drag = null
                this.image?.removeAttribute('data-dragging')
            }, { signal })
        }

        // load and error don't bubble: listen while they go down.
        const loaded = event => event.target === this.image && this.dialog.removeAttribute('data-loading')

        this.addEventListener('load', loaded, { capture: true, signal })
        this.addEventListener('error', loaded, { capture: true, signal })
    }

    disconnectedCallback() {
        disconnected(this)
    }

    get dialog() {
        return this.querySelector(':scope > [data-slot="image-preview-content"]')
    }

    get image() {
        return this.dialog?.querySelector('[data-slot="image-preview-image"]')
    }

    get index() {
        return this.#index
    }

    open(index = 0) {
        const triggers = this.#triggers()
        const trigger = triggers[index]

        if (! trigger || ! this.dialog) return

        this.#index = index
        this.#scale = 1
        this.#rotate = 0
        this.#x = this.#y = 0

        const image = this.image

        if (image.getAttribute('src') !== trigger.dataset.src) {
            this.dialog.toggleAttribute('data-loading', true)
            image.src = trigger.dataset.src
        }

        image.alt = trigger.dataset.alt ?? ''

        this.dialog.toggleAttribute('data-single', triggers.length < 2)
        this.dialog.querySelector('[data-slot="image-preview-counter"]').textContent = `${index + 1} / ${triggers.length}`
        this.#button('previous').disabled = index === 0
        this.#button('next').disabled = index === triggers.length - 1
        this.#apply()

        if (! this.dialog.open) this.dialog.showModal()
    }

    #triggers() {
        return [...this.querySelectorAll('[data-slot="image-preview-trigger"]')]
    }

    #button(action) {
        return this.dialog.querySelector(`[data-image-preview="${action}"]`)
    }

    #act(action) {
        if (action === 'close') this.dialog.close()
        else if (action === 'previous' && this.#index > 0) this.open(this.#index - 1)
        else if (action === 'next' && this.#index < this.#triggers().length - 1) this.open(this.#index + 1)
        else if (action === 'zoom-in') this.#zoom(this.#scale * FACTOR)
        else if (action === 'zoom-out') this.#zoom(this.#scale / FACTOR)
        else if (action === 'rotate-left') this.#turn(-90)
        else if (action === 'rotate-right') this.#turn(90)
        else if (action === 'reset') this.#reset()
    }

    #zoom(scale) {
        this.#scale = Math.min(MAX, Math.max(MIN, Math.round(scale * 1000) / 1000))

        // Back at its size, the image recenters.
        if (this.#scale <= 1) this.#x = this.#y = 0

        this.#apply()
    }

    #turn(degrees) {
        this.#rotate += degrees
        this.#apply()
    }

    #reset() {
        this.#scale = 1
        this.#rotate = 0
        this.#x = this.#y = 0
        this.#apply()
    }

    #apply() {
        const image = this.image

        image.style.setProperty('--image-preview-scale', this.#scale)
        image.style.setProperty('--image-preview-rotate', `${this.#rotate}deg`)
        image.style.setProperty('--image-preview-x', `${this.#x}px`)
        image.style.setProperty('--image-preview-y', `${this.#y}px`)
        image.toggleAttribute('data-zoomed', this.#scale > 1)

        this.#button('zoom-in').disabled = this.#scale >= MAX
        this.#button('zoom-out').disabled = this.#scale <= MIN
        this.#button('reset').disabled = this.#scale === 1 && this.#rotate === 0 && ! this.#x && ! this.#y
    }
}

export function register() {
    define('eso-image-preview', ImagePreviewElement)
}
