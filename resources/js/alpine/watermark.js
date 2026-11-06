/*
 * <eso-watermark>
 *
 * Keeps the layer the view rendered: removing it or changing it (in the browser's dev
 * tools, say) puts the original back, in its place. What Livewire's morph does to the
 * layer becomes the original, so a watermark can still change with the page.
 */

const LAYER = '[data-slot="watermark-layer"]'


export default (binding = {}) => ({
    _hooks: null,
    _original: null,
    _after: null,
    _observer: null,

    init() {
        this._observer = new MutationObserver(() => this._restore())
        if (! this._original) this.accept(this.$el.querySelector(`:scope > ${LAYER}`))
        this._watch()
        const accept = (layer, removed = false) => {
            if (!layer.matches?.(LAYER) || layer.parentElement !== this.$el) return
            this.accept(removed ? [...this.$el.querySelectorAll(':scope > '+LAYER)].find(other => other !== layer) ?? null : layer)
        }
        this._hooks = [
            window.Livewire.hook('morph.updated', ({ el }) => accept(el)),
            window.Livewire.hook('morph.added', ({ el }) => accept(el)),
            window.Livewire.hook('morph.removing', ({ el }) => accept(el, true)),
        ]
    },

    destroy() {
        this._hooks?.forEach(stop => stop())
        this._observer.disconnect()
    },

    /**
     * Takes this layer (or none, with null) as the watermark.
     */
    accept(layer) {
        this._original = layer?.cloneNode(true) ?? null
        this._after = layer?.nextSibling ?? null
    },

    _watch() {
        this._observer.observe(this.$el, { childList: true, subtree: true, attributes: true, characterData: true })
    },

    _restore() {
        const [layer, ...extra] = this.$el.querySelectorAll(`:scope > ${LAYER}`)

        if (this._original ? layer?.isEqualNode(this._original) && ! extra.length : ! layer) return

        this._observer.disconnect()
        extra.forEach(element => element.remove())

        // In place: Livewire finds conditional markup between comment markers.
        if (! this._original) layer?.remove()
        else if (layer) layer.replaceWith(this._original.cloneNode(true))
        else this.$el.insertBefore(this._original.cloneNode(true), this._after?.parentNode === this.$el ? this._after : null)

        this._watch()
    },
})
