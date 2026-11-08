/*
 * <eso-password-input>
 *
 * The eye toggles the input between password and text.
 */

import { disconnected, listen } from './interaction.js'

// The eye shows or hides the password; morphs put back type="password", so it is reapplied.
export default () => ({
    _observer: null,
    _visible: null,
    init() {
        this._visible = false
        this._observer = new MutationObserver(() => this._render())

        listen(this, this.$el, 'click', event => {
            if (! event.target.closest?.('[data-password-toggle]')) return

            this._visible = ! this._visible
            this._render()
        }, {})

        this._render()
        this._observer.observe(this.$el, { subtree: true, childList: true, attributes: true, attributeFilter: ['type', 'aria-pressed'] })

    },
    destroy() {
        disconnected(this)
        this._observer.disconnect()

        disconnected(this)
    },
    get visible() {
        return this._visible
    },

    _render() {
        const input = this.$el.querySelector('[data-slot="input-group-control"]')
        const toggle = this.$el.querySelector('[data-password-toggle]')
        const type = this._visible ? 'text' : 'password'

        if (input && input.getAttribute('type') !== type) input.setAttribute('type', type)
        if (toggle && toggle.getAttribute('aria-pressed') !== String(this._visible)) toggle.setAttribute('aria-pressed', String(this._visible))
    },
})
