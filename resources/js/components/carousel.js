/*
 * <eso-carousel>
 *
 * The content scrolls natively with scroll snapping (touch, trackpad and wheel work as
 * usual). Previous / Next and the arrow keys move one slide, and the buttons disable
 * at either end, as with shadcn's Embla carousel.
 */

import { connected, define, disconnected } from '../support/element.js'

export class CarouselElement extends HTMLElement {
    #frame = 0
    #resize = new ResizeObserver(() => this.#schedule())

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('click', event => {
            const button = event.target.closest?.('[data-carousel]')

            if (button && button.closest('eso-carousel') === this) button.dataset.carousel === 'next' ? this.scrollNext() : this.scrollPrev()
        }, { signal })

        // Like Embla's handler, Left/Right move slides whatever the orientation.
        this.addEventListener('keydown', event => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return

            event.preventDefault()
            event.key === 'ArrowRight' ? this.scrollNext() : this.scrollPrev()
        }, { capture: true, signal })

        this.viewport?.addEventListener('scroll', () => this.#schedule(), { passive: true, signal })
        if (this.viewport) this.#resize.observe(this.viewport)
        this.#schedule()
    }

    disconnectedCallback() {
        disconnected(this)
        this.#resize.disconnect()
        cancelAnimationFrame(this.#frame)
    }

    get vertical() {
        return this.dataset.orientation === 'vertical'
    }

    get viewport() {
        return this.querySelector(':scope > [data-slot="carousel-content"]')
    }

    get canScrollPrev() {
        const viewport = this.viewport

        return Boolean(viewport) && Math.abs(this.vertical ? viewport.scrollTop : viewport.scrollLeft) > 1
    }

    get canScrollNext() {
        const viewport = this.viewport

        if (! viewport) return false

        return this.vertical
            ? viewport.scrollTop < viewport.scrollHeight - viewport.clientHeight - 1
            : Math.abs(viewport.scrollLeft) < viewport.scrollWidth - viewport.clientWidth - 1
    }

    scrollPrev() {
        this.#step(-1)
    }

    scrollNext() {
        this.#step(1)
    }

    // One slide: the first slide's size, padding included.
    #step(direction) {
        const viewport = this.viewport
        const slide = viewport?.querySelector('[data-slot="carousel-item"]')

        if (! slide) return

        const distance = (this.vertical ? slide.offsetHeight : slide.offsetWidth) * direction
        const rtl = ! this.vertical && getComputedStyle(viewport).direction === 'rtl'

        viewport.scrollBy({ [this.vertical ? 'top' : 'left']: rtl ? -distance : distance, behavior: 'smooth' })
    }

    #schedule() {
        cancelAnimationFrame(this.#frame)
        this.#frame = requestAnimationFrame(() => this.update())
    }

    update() {
        for (const button of this.querySelectorAll(':scope > [data-carousel]')) {
            const disabled = ! (button.dataset.carousel === 'next' ? this.canScrollNext : this.canScrollPrev)

            if (button.disabled !== disabled) button.disabled = disabled
        }
    }
}

export function register() {
    define('eso-carousel', CarouselElement)
}
