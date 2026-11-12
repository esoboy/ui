/*
 * <eso-anchor>
 *
 * Marks the item of the section being read aria-current="location": the last one whose
 * target starts within the top fifth of the window (shadcn's docs read the same band),
 * or the last item once its scroller is at the bottom, where later sections can't reach
 * the band. Scrolls of any element count, so sections in a scrolling container work too.
 */

import { disconnected, listen, unlisten } from './interaction.js'

export default () => ({
    _frame: null,
    _observer: null,
    _schedule: null,
    _scroller: null,
    init() {
        this._frame = 0
        this._scroller = null
        this._observer = new MutationObserver(() => this._update())
        this._schedule = event => {
        if (event?.type === 'scroll') this._scroller = event.target === document ? document.scrollingElement : event.target

        cancelAnimationFrame(this._frame)
        this._frame = requestAnimationFrame(() => this._update())
    }
        listen(this, document, 'scroll', this._schedule, { capture: true, passive: true })
        listen(this, window, 'resize', this._schedule, { passive: true })

        this._update()
        // Morphs drop aria-current, which the server doesn't render: put it back.
        this._observer.observe(this.$el, { subtree: true, childList: true, attributes: true, attributeFilter: ['aria-current', 'href'] })

    },
    destroy() {
        unlisten(document, 'scroll', this._schedule, { capture: true })
        unlisten(window, 'resize', this._schedule)
        cancelAnimationFrame(this._frame)
        this._observer.disconnect()

        disconnected(this)
    },
    _update() {
        const sections = [...this.$el.querySelectorAll('[data-slot="anchor-item"]')]
            .map(item => [item, item.hash && document.getElementById(decodeURIComponent(item.hash.slice(1)))])
            .filter(([, target]) => target)

        const band = window.innerHeight / 5
        let [active] = sections.findLast(([, target]) => target.getBoundingClientRect().top <= band) ?? []

        const scroller = this._scroller ?? document.scrollingElement
        const [last, lastTarget] = sections.at(-1) ?? []

        if (lastTarget && scroller?.contains(lastTarget) && scroller.scrollTop > 0 && scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1) {
            active = last
        }

        for (const [item] of sections) {
            if (item === active && item.getAttribute('aria-current') !== 'location') item.setAttribute('aria-current', 'location')
            if (item !== active && item.hasAttribute('aria-current')) item.removeAttribute('aria-current')
        }
    },
})
