/*
 * <eso-carousel>
 *
 * The content scrolls natively with scroll snapping (touch, trackpad and wheel work as
 * usual). Previous / Next and the arrow keys move one slide, and the buttons disable
 * at either end, as with shadcn's Embla carousel.
 */

import { disconnected, listen } from './interaction.js'

export default () => ({
    _frame: null,
    _resize: null,
    init() {
        this._frame = 0
        this._resize = new ResizeObserver(() => this._schedule())

        listen(this, this.$el, 'click', event => {
            const button = event.target.closest?.('[data-carousel]')

            if (button && button.closest('eso-carousel') === this.$el) button.dataset.carousel === 'next' ? this.scrollNext() : this.scrollPrev()
        }, {})

        // Like Embla's handler, Left/Right move slides whatever the orientation.
        listen(this, this.$el, 'keydown', event => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return

            event.preventDefault()
            event.key === 'ArrowRight' ? this.scrollNext() : this.scrollPrev()
        }, { capture: true })

        listen(this, this.viewport, 'scroll', () => this._schedule(), { passive: true })
        if (this.viewport) this._resize.observe(this.viewport)
        this._schedule()

    },
    destroy() {
        disconnected(this)
        this._resize.disconnect()
        cancelAnimationFrame(this._frame)

        disconnected(this)
    },
    get vertical() {
        return this.$el.dataset.orientation === 'vertical'
    },

    get viewport() {
        return this.$el.querySelector(':scope > [data-slot="carousel-content"]')
    },

    get canScrollPrev() {
        const viewport = this.viewport

        return Boolean(viewport) && Math.abs(this.vertical ? viewport.scrollTop : viewport.scrollLeft) > 1
    },

    get canScrollNext() {
        const viewport = this.viewport

        if (! viewport) return false

        return this.vertical
            ? viewport.scrollTop < viewport.scrollHeight - viewport.clientHeight - 1
            : Math.abs(viewport.scrollLeft) < viewport.scrollWidth - viewport.clientWidth - 1
    },

    scrollPrev() {
        this._step(-1)
    },

    scrollNext() {
        this._step(1)
    },

    _step(direction) {
        const viewport = this.viewport
        const slide = viewport?.querySelector('[data-slot="carousel-item"]')

        if (! slide) return

        const distance = (this.vertical ? slide.offsetHeight : slide.offsetWidth) * direction
        const rtl = ! this.vertical && getComputedStyle(viewport).direction === 'rtl'

        viewport.scrollBy({ [this.vertical ? 'top' : 'left']: rtl ? -distance : distance, behavior: 'smooth' })
    },

    _schedule() {
        cancelAnimationFrame(this._frame)
        this._frame = requestAnimationFrame(() => this.update())
    },

    update() {
        for (const button of this.$el.querySelectorAll(':scope > [data-carousel]')) {
            const disabled = ! (button.dataset.carousel === 'next' ? this.canScrollNext : this.canScrollPrev)

            if (button.disabled !== disabled) button.disabled = disabled
        }
    },
})
