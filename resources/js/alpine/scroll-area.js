/*
 * <eso-scroll-area>
 *
 * The viewport scrolls natively with its scrollbars hidden; this draws shadcn's thin
 * scrollbars over it on an axis that overflows, visible while hovered or scrolling.
 * Thumbs drag, and a press on the track jumps there, as in Base UI.
 */

import { disconnected, listen, unlisten } from './interaction.js'

const MIN_THUMB = 16
const SCROLL_TIMEOUT = 500

export default () => ({
    _frame: null,
    _mutations: null,
    _resize: null,
    _scrolling: null,
    init() {
        this._resize = new ResizeObserver(() => this.update())
        this._mutations = new MutationObserver(() => this._observe())
        this._frame = 0
        this._scrolling = 0

        listen(this, this.viewport, 'scroll', () => this._onScroll(), { passive: true })

        for (const bar of this._bars()) {
            listen(this, bar, 'pointerdown', event => this._onPress(event, bar), {})
        }

        this._observe()
        this._mutations.observe(this.viewport ?? this.$el, { childList: true })

    },
    destroy() {
        disconnected(this)
        this._resize.disconnect()
        this._mutations.disconnect()
        cancelAnimationFrame(this._frame)
        clearTimeout(this._scrolling)

        disconnected(this)
    },
    get viewport() {
        return this.$el.querySelector(':scope > [data-slot="scroll-area-viewport"]')
    },

    scrollbar(orientation) {
        return this.$el.querySelector(`:scope > [data-slot="scroll-area-scrollbar"][data-orientation="${orientation}"]`)
    },

    update() {
        const viewport = this.viewport

        if (! viewport) return

        const vertical = this.scrollbar('vertical')
        const horizontal = this.scrollbar('horizontal')
        const overflowY = viewport.scrollHeight > viewport.clientHeight
        const overflowX = viewport.scrollWidth > viewport.clientWidth

        if (vertical && vertical.hidden === overflowY) vertical.hidden = ! overflowY
        if (horizontal && horizontal.hidden === overflowX) horizontal.hidden = ! overflowX

        this.$el.style.setProperty('--scroll-area-corner-height', overflowX && overflowY ? `${horizontal.offsetHeight}px` : '0px')
        this.$el.style.setProperty('--scroll-area-corner-width', overflowX && overflowY ? `${vertical.offsetWidth}px` : '0px')

        if (overflowY) this._place(vertical, 'height', viewport.clientHeight, viewport.scrollHeight, viewport.scrollTop, 'translateY')
        if (overflowX) this._place(horizontal, 'width', viewport.clientWidth, viewport.scrollWidth, Math.abs(viewport.scrollLeft), 'translateX')
    },

    _place(bar, dimension, visible, total, offset, translate) {
        const track = this._track(bar, dimension)
        const size = Math.max(MIN_THUMB, track * visible / total)
        const position = (track - size) * offset / (total - visible)
        const thumb = bar.firstElementChild

        bar.style.setProperty(`--scroll-area-thumb-${dimension}`, `${size}px`)
        if (thumb) thumb.style.transform = `${translate}(${position}px)`
    },

    _track(bar, dimension) {
        const style = getComputedStyle(bar)

        return dimension === 'height'
            ? bar.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom)
            : bar.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight)
    },

    _bars() {
        return [this.scrollbar('vertical'), this.scrollbar('horizontal')].filter(Boolean)
    },

    _observe() {
        this._resize.disconnect()

        if (! this.viewport) return

        this._resize.observe(this.viewport)
        for (const child of this.viewport.children) this._resize.observe(child)

        this._schedule()
    },

    _onScroll() {
        this.$el.setAttribute('data-scrolling', '')
        clearTimeout(this._scrolling)
        this._scrolling = setTimeout(() => this.$el.removeAttribute('data-scrolling'), SCROLL_TIMEOUT)
        this._schedule()
    },

    _schedule() {
        cancelAnimationFrame(this._frame)
        this._frame = requestAnimationFrame(() => this.update())
    },

    _onPress(event, bar) {
        if (event.button !== 0) return

        event.preventDefault()

        const viewport = this.viewport
        const vertical = bar.dataset.orientation === 'vertical'
        const thumb = bar.firstElementChild
        const dimension = vertical ? 'height' : 'width'
        const track = this._track(bar, dimension)
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
            unlisten(bar, 'pointermove', move)
            unlisten(bar, 'pointerup', stop)
            unlisten(bar, 'pointercancel', stop)
        }

        bar.setPointerCapture?.(event.pointerId)
        bar.setAttribute('data-dragging', '')
        listen(this, bar, 'pointermove', move)
        listen(this, bar, 'pointerup', stop)
        listen(this, bar, 'pointercancel', stop)
    },
})
