/*
 * <eso-watermark>
 *
 * Keeps the layer the view rendered: removing it or changing it (in the browser's dev
 * tools, say) puts the original back, in its place. What Livewire's morph does to the
 * layer becomes the original, so a watermark can still change with the page.
 */

import { define } from '../support/element.js'
import { onLivewire } from '../support/livewire.js'

const LAYER = '[data-slot="watermark-layer"]'

export class WatermarkElement extends HTMLElement {
    #original = null
    #after = null
    #observer = new MutationObserver(() => this.#restore())

    connectedCallback() {
        if (! this.#original) this.accept(this.querySelector(`:scope > ${LAYER}`))
        this.#watch()
    }

    disconnectedCallback() {
        this.#observer.disconnect()
    }

    /**
     * Takes this layer (or none, with null) as the watermark.
     */
    accept(layer) {
        this.#original = layer?.cloneNode(true) ?? null
        this.#after = layer?.nextSibling ?? null
    }

    #watch() {
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, characterData: true })
    }

    #restore() {
        const [layer, ...extra] = this.querySelectorAll(`:scope > ${LAYER}`)

        if (this.#original ? layer?.isEqualNode(this.#original) && ! extra.length : ! layer) return

        this.#observer.disconnect()
        extra.forEach(element => element.remove())

        // In place: Livewire finds conditional markup between comment markers.
        if (! this.#original) layer?.remove()
        else if (layer) layer.replaceWith(this.#original.cloneNode(true))
        else this.insertBefore(this.#original.cloneNode(true), this.#after?.parentNode === this ? this.#after : null)

        this.#watch()
    }
}

export function register() {
    define('eso-watermark', WatermarkElement)

    // These run while the morph patches the layer, before the observer sees the result;
    // the morphed hook would come too late, after Livewire awaits the DOM update.
    const accept = (layer, removed = false) => {
        const watermark = layer.parentElement

        if (! layer.matches?.(LAYER) || ! (watermark instanceof WatermarkElement)) return

        // A layer on its way out leaves the one the morph added, if any.
        watermark.accept(removed ? [...watermark.querySelectorAll(`:scope > ${LAYER}`)].find(other => other !== layer) ?? null : layer)
    }

    onLivewire(Livewire => {
        Livewire.hook('morph.updated', ({ el }) => accept(el))
        Livewire.hook('morph.added', ({ el }) => accept(el))
        Livewire.hook('morph.removing', ({ el }) => accept(el, true))
    })
}
