/*
 * <eso-back-to-top>
 *
 * Shows once the window (or data-target, a selector) has scrolled past data-threshold,
 * and scrolls it back to the top, at once when the user prefers reduced motion.
 */

import { disconnected, listen, unlisten } from './interaction.js'

export default () => ({
    _listening: null,
    _scrollUp: null,
    _update: null,
    init() {
        this._listening = null
        this._update = () => {
        this.$el.hidden = this._top() < (Number(this.$el.dataset.threshold) || 0)
    }
        this._scrollUp = () => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches

        this._scroller().scrollTo({ top: 0, behavior: reduce ? 'instant' : 'smooth' })
    }
        this._listening = this._scroller()
        listen(this, this._listening, 'scroll', this._update, { passive: true })
        this._update()

        listen(this, this.$el, 'click', this._scrollUp)

    },
    destroy() {
        unlisten(this._listening, 'scroll', this._update)
        unlisten(this.$el, 'click', this._scrollUp)

        disconnected(this)
    },
    _scroller() {
        return (this.$el.dataset.target && document.querySelector(this.$el.dataset.target)) || window
    },

    _top() {
        const scroller = this._scroller()

        return scroller === window ? window.scrollY : scroller.scrollTop
    },
})
