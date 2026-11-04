/*
 * <eso-anchor>
 *
 * Marks the item of the section being read aria-current="location": the last one whose
 * target starts within the top fifth of the window (shadcn's docs read the same band),
 * or the last item once its scroller is at the bottom, where later sections can't reach
 * the band. Scrolls of any element count, so sections in a scrolling container work too.
 */

import { define } from '../support/element.js'

export class AnchorElement extends HTMLElement {
    #frame = 0
    #scroller = null
    #observer = new MutationObserver(() => this.#update())

    #schedule = event => {
        if (event?.type === 'scroll') this.#scroller = event.target === document ? document.scrollingElement : event.target

        cancelAnimationFrame(this.#frame)
        this.#frame = requestAnimationFrame(() => this.#update())
    }

    connectedCallback() {
        document.addEventListener('scroll', this.#schedule, { capture: true, passive: true })
        window.addEventListener('resize', this.#schedule, { passive: true })

        this.#update()
        // Morphs drop aria-current, which the server doesn't render: put it back.
        this.#observer.observe(this, { subtree: true, childList: true, attributes: true, attributeFilter: ['aria-current', 'href'] })
    }

    disconnectedCallback() {
        document.removeEventListener('scroll', this.#schedule, { capture: true })
        window.removeEventListener('resize', this.#schedule)
        cancelAnimationFrame(this.#frame)
        this.#observer.disconnect()
    }

    #update() {
        const sections = [...this.querySelectorAll('[data-slot="anchor-item"]')]
            .map(item => [item, item.hash && document.getElementById(decodeURIComponent(item.hash.slice(1)))])
            .filter(([, target]) => target)

        const band = window.innerHeight / 5
        let [active] = sections.findLast(([, target]) => target.getBoundingClientRect().top <= band) ?? []

        const scroller = this.#scroller ?? document.scrollingElement
        const [last, lastTarget] = sections.at(-1) ?? []

        if (lastTarget && scroller?.contains(lastTarget) && scroller.scrollTop > 0 && scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1) {
            active = last
        }

        for (const [item] of sections) {
            if (item === active && item.getAttribute('aria-current') !== 'location') item.setAttribute('aria-current', 'location')
            if (item !== active && item.hasAttribute('aria-current')) item.removeAttribute('aria-current')
        }
    }
}

export function register() {
    define('eso-anchor', AnchorElement)
}
