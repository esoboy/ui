/*
 * <eso-transfer>
 *
 * Checked items move to the other list with the arrow buttons. Each list's header
 * checkbox checks its shown items and its search filters them; both lists keep the
 * options' order. .value is the right list's values; input/change are dispatched
 * from the transfer, so wire:model binds on it.
 */

import { announce, connected, define, disconnected, readValue, writeHidden } from '../support/element.js'

export class TransferElement extends HTMLElement {
    #value = []
    #checked = new Set()
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        const value = readValue(this, [])

        this.#value = Array.isArray(value) ? value.map(String) : []

        // The checkboxes' and searches' own input/change stay inside: the transfer announces its value.
        this.addEventListener('input', event => {
            if (event.target === this) return

            event.stopPropagation()
            if (event.target.matches('[data-transfer-search]')) this.#render()
        }, { signal })

        this.addEventListener('change', event => {
            if (event.target === this) return

            event.stopPropagation()

            const checkbox = event.target

            if (checkbox.matches('[data-transfer-all]')) {
                for (const item of this.#items(checkbox.closest('[data-slot="transfer-list"]')).filter(this.#available)) {
                    checkbox.checked ? this.#checked.add(item.dataset.value) : this.#checked.delete(item.dataset.value)
                }
            } else if (checkbox.closest('[data-slot="transfer-item"]')) {
                const value = checkbox.closest('[data-slot="transfer-item"]').dataset.value

                checkbox.checked ? this.#checked.add(value) : this.#checked.delete(value)
            }

            this.#render()
        }, { signal })

        this.addEventListener('click', event => {
            const move = event.target.closest?.('[data-transfer-move]')

            if (move && ! move.disabled) this.#move(move.dataset.transferMove)
        }, { signal })

        this.#render()

        // Livewire morphs may put items back where the server had them: place ours.
        this.#observer.observe(this, { subtree: true, childList: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get value() {
        return [...this.#value]
    }

    set value(value) {
        this.#value = Array.isArray(value) ? value.map(String) : []
        this.#render()
    }

    #list(name) {
        return this.querySelector(`[data-slot="transfer-list"][data-list="${name}"]`)
    }

    #items(list) {
        return list ? [...list.querySelectorAll('[data-slot="transfer-item"]')] : []
    }

    #available = item => ! item.hidden && ! item.querySelector('input').disabled

    #move(to) {
        const moving = this.#items(this.#list(to === 'target' ? 'source' : 'target'))
            .filter(item => this.#checked.has(item.dataset.value) && ! item.querySelector('input').disabled)
            .map(item => item.dataset.value)

        if (! moving.length) return

        moving.forEach(value => this.#checked.delete(value))

        const chosen = new Set(to === 'target' ? [...this.#value, ...moving] : this.#value.filter(value => ! moving.includes(value)))

        this.#value = this.#ordered().map(item => item.dataset.value).filter(value => chosen.has(value))
        this.#render()
        announce(this)
    }

    #ordered() {
        return [...this.querySelectorAll('[data-slot="transfer-item"]')].sort((a, b) => a.dataset.index - b.dataset.index)
    }

    #render() {
        this.#observer.disconnect()

        const items = this.#ordered()
        const chosen = new Set(this.#value)

        for (const name of ['source', 'target']) {
            const list = this.#list(name)
            const container = list?.querySelector('[data-slot="transfer-items"]')

            if (! container) continue

            // Place the list's items in the options' order.
            const wanted = items.filter(item => chosen.has(item.dataset.value) === (name === 'target'))

            if (wanted.some((item, index) => container.children[index] !== item) || container.children.length !== wanted.length) container.append(...wanted)

            const query = list.querySelector('[data-transfer-search]')?.value.trim().toLowerCase() ?? ''

            for (const item of wanted) {
                const checkbox = item.querySelector('input')
                const hidden = query !== '' && ! item.textContent.toLowerCase().includes(query)

                if (item.hidden !== hidden) item.hidden = hidden
                if (checkbox.checked !== this.#checked.has(item.dataset.value)) checkbox.checked = this.#checked.has(item.dataset.value)
            }

            const checked = wanted.filter(item => this.#checked.has(item.dataset.value))
            const shown = wanted.filter(this.#available)
            const all = list.querySelector('[data-transfer-all]')
            const count = list.querySelector('[data-slot="transfer-count"]')
            const text = `${checked.length}/${wanted.length}`

            if (count && count.textContent !== text) count.textContent = text

            if (all) {
                const every = shown.length > 0 && shown.every(item => this.#checked.has(item.dataset.value))

                all.checked = every
                all.indeterminate = ! every && shown.some(item => this.#checked.has(item.dataset.value))
                all.disabled = this.hasAttribute('data-disabled') || shown.length === 0
            }

            list.toggleAttribute('data-empty', ! wanted.some(item => ! item.hidden))

            const move = this.querySelector(`[data-transfer-move="${name === 'source' ? 'target' : 'source'}"]`)

            if (move) move.disabled = this.hasAttribute('data-disabled') || ! checked.length
        }

        writeHidden(this, this.dataset.name, this.#value)
        this.#observer.observe(this, { subtree: true, childList: true })
    }
}

export function register() {
    define('eso-transfer', TransferElement)
}
