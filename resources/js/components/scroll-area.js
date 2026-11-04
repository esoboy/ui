/*
 * <eso-scroll-area>
 *
 * The viewport scrolls natively with its scrollbars hidden; this draws shadcn's thin
 * scrollbars over it on an axis that overflows, visible while hovered or scrolling.
 * Thumbs drag, and a press on the track jumps there, as in Base UI.
 */

import { connected, define, disconnected } from '../support/element.js'

const MIN_THUMB = 16
const SCROLL_TIMEOUT = 500

export class ScrollAreaElement extends HTMLElement {
    #resize = new ResizeObserver(() => this.update())
    #mutations = new MutationObserver(() => this.#observe())
    #frame = 0
    #scrolling = 0

    connectedCallback() {
        const signal = connected(this)

        this.viewport?.addEventListener('scroll', () => this.#onScroll(), { passive: true, signal })

        for (const bar of this.#bars()) {
            bar.addEventListener('pointerdown', event => this.#onPress(event, bar), { signal })
        }

        this.#observe()
        this.#mutations.observe(this.viewport ?? this, { childList: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#resize.disconnect()
        this.#mutations.disconnect()
        cancelAnimationFrame(this.#frame)
        clearTimeout(this.#scrolling)
    }

    get viewport() {
        return this.querySelector(':scope > [data-slot="scroll-area-viewport"]')
    }

    scrollbar(orientation) {
        return this.querySelector(`:scope > [data-slot="scroll-area-scrollbar"][data-orientation="${orientation}"]`)
    }

    update() {
        const viewport = this.viewport

        if (! viewport) return

        const vertical = this.scrollbar('vertical')
        const horizontal = this.scrollbar('horizontal')
        const overflowY = viewport.scrollHeight > viewport.clientHeight
        const overflowX = viewport.scrollWidth > viewport.clientWidth

        if (vertical && vertical.hidden === overflowY) vertical.hidden = ! overflowY
        if (horizontal && horizontal.hidden === overflowX) horizontal.hidden = ! overflowX

        this.style.setProperty('--scroll-area-corner-height', overflowX && overflowY ? `${horizontal.offsetHeight}px` : '0px')
        this.style.setProperty('--scroll-area-corner-width', overflowX && overflowY ? `${vertical.offsetWidth}px` : '0px')

        if (overflowY) this.#place(vertical, 'height', viewport.clientHeight, viewport.scrollHeight, viewport.scrollTop, 'translateY')
        if (overflowX) this.#place(horizontal, 'width', viewport.clientWidth, viewport.scrollWidth, Math.abs(viewport.scrollLeft), 'translateX')
    }

    #place(bar, dimension, visible, total, offset, translate) {
        const track = this.#track(bar, dimension)
        const size = Math.max(MIN_THUMB, track * visible / total)
        const position = (track - size) * offset / (total - visible)
        const thumb = bar.firstElementChild

        bar.style.setProperty(`--scroll-area-thumb-${dimension}`, `${size}px`)
        if (thumb) thumb.style.transform = `${translate}(${position}px)`
    }

    #track(bar, dimension) {
        const style = getComputedStyle(bar)

        return dimension === 'height'
            ? bar.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom)
            : bar.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight)
    }

    #bars() {
        return [this.scrollbar('vertical'), this.scrollbar('horizontal')].filter(Boolean)
    }

    // Size changes of the viewport or anything in it move the thumbs.
    #observe() {
        this.#resize.disconnect()

        if (! this.viewport) return

        this.#resize.observe(this.viewport)
        for (const child of this.viewport.children) this.#resize.observe(child)

        this.#schedule()
    }

    // The scrollbars show while scrolling and for a moment after.
    #onScroll() {
        this.setAttribute('data-scrolling', '')
        clearTimeout(this.#scrolling)
        this.#scrolling = setTimeout(() => this.removeAttribute('data-scrolling'), SCROLL_TIMEOUT)
        this.#schedule()
    }

    #schedule() {
        cancelAnimationFrame(this.#frame)
        this.#frame = requestAnimationFrame(() => this.update())
    }

    #onPress(event, bar) {
        if (event.button !== 0) return

        event.preventDefault()

        const viewport = this.viewport
        const vertical = bar.dataset.orientation === 'vertical'
        const thumb = bar.firstElementChild
        const dimension = vertical ? 'height' : 'width'
        const track = this.#track(bar, dimension)
        const thumbSize = vertical ? thumb.offsetHeight : thumb.offsetWidth
        const scrollable = vertical ? viewport.scrollHeight - viewport.clientHeight : viewport.scrollWidth - viewport.clientWidth
        const ratio = scrollable / Math.max(track - thumbSize, 1)
        const point = e => (vertical ? e.clientY : e.clientX)
        const scroll = value => (vertical ? (viewport.scrollTop = value) : (viewport.scrollLeft = value))

        // A press on the track centres the thumb there first.
        if (event.target !== thumb) {
            const box = bar.getBoundingClientRect()
            scroll((point(event) - (vertical ? box.top : box.left) - thumbSize / 2) * ratio)
        }

        const start = point(event)
        const from = vertical ? viewport.scrollTop : viewport.scrollLeft

        const move = e => scroll(from + (point(e) - start) * ratio)
        const stop = () => {
            bar.removeAttribute('data-dragging')
            bar.removeEventListener('pointermove', move)
            bar.removeEventListener('pointerup', stop)
            bar.removeEventListener('pointercancel', stop)
        }

        bar.setPointerCapture?.(event.pointerId)
        bar.setAttribute('data-dragging', '')
        bar.addEventListener('pointermove', move)
        bar.addEventListener('pointerup', stop)
        bar.addEventListener('pointercancel', stop)
    }
}

export function register() {
    define('eso-scroll-area', ScrollAreaElement)
}
