/*
 * <eso-back-to-top>
 *
 * Shows once the window (or data-target, a selector) has scrolled past data-threshold,
 * and scrolls it back to the top, at once when the user prefers reduced motion.
 */

import { define } from '../support/element.js'

export class BackToTopElement extends HTMLElement {
    #listening = null
    #update = () => {
        this.hidden = this.#top() < (Number(this.dataset.threshold) || 0)
    }

    connectedCallback() {
        this.#listening = this.#scroller()
        this.#listening.addEventListener('scroll', this.#update, { passive: true })
        this.#update()

        this.addEventListener('click', this.#scrollUp)
    }

    disconnectedCallback() {
        this.#listening?.removeEventListener('scroll', this.#update)
        this.removeEventListener('click', this.#scrollUp)
    }

    #scrollUp = () => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches

        this.#scroller().scrollTo({ top: 0, behavior: reduce ? 'instant' : 'smooth' })
    }

    #scroller() {
        return (this.dataset.target && document.querySelector(this.dataset.target)) || window
    }

    #top() {
        const scroller = this.#scroller()

        return scroller === window ? window.scrollY : scroller.scrollTop
    }
}

export function register() {
    define('eso-back-to-top', BackToTopElement)
}
