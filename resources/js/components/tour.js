/*
 * <eso-tour>
 *
 * Shows its steps one after the other. The step's target (a CSS selector) carries the
 * anchor --tour-target, which the spotlight and the panel follow; the panel shows the
 * step's content, cloned from its template, a counter and Previous / Next or Finish.
 * Started by a button (commandfor="<name>" command="--start") or Ui::tour('<name>')->start();
 * closed by its close button, Escape, Finish, command="--close" or ->close(). From the
 * element, tour-change reports { step } and tour-close { step, finished }.
 */

import { connected, define, disconnected } from '../support/element.js'
import { onLivewire } from '../support/livewire.js'

const ANCHOR = '--tour-target'

export class TourElement extends HTMLElement {
    #step = -1
    #target = null
    #returnFocus = null

    connectedCallback() {
        const signal = connected(this)

        // Invoker commands: <button commandfor="<name>" command="--start">.
        this.addEventListener('command', event => {
            if (event.command === '--start') this.start()
            if (event.command === '--close') this.close()
        }, { signal })

        this.addEventListener('click', event => {
            const action = event.target.closest?.('[data-tour-action]')?.dataset.tourAction

            if (action === 'previous') this.go(this.#step - 1)
            if (action === 'next') this.go(this.#step + 1)
            if (action === 'finish') this.close(true)
            if (action === 'close') this.close()
        }, { signal })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#point(null)
    }

    get steps() {
        return [...this.querySelectorAll(':scope > template[data-tour-step]')]
    }

    get content() {
        return this.querySelector(':scope > [data-slot="tour-content"]')
    }

    get spotlight() {
        return this.querySelector(':scope > [data-slot="tour-spotlight"]')
    }

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    }

    get step() {
        return this.#step
    }

    start(step = 0) {
        if (! this.steps.length || ! this.content) return

        if (! this.isOpen) {
            this.#returnFocus = document.activeElement
            this.spotlight?.showPopover()
            this.content.showPopover()
        }

        this.go(step)
    }

    go(index) {
        const step = this.steps[index]

        if (! step || ! this.isOpen) return

        const content = this.content
        const body = content.querySelector('[data-slot="tour-body"]')
        const last = index === this.steps.length - 1
        const action = name => content.querySelector(`[data-tour-action="${name}"]`)

        this.#step = index
        this.#point(this.#find(step), { scroll: true })

        body.replaceChildren(step.content.cloneNode(true))
        this.#label(body.querySelector('[data-slot="tour-title"]'), 'aria-labelledby', 'title')
        this.#label(body.querySelector('[data-slot="tour-description"]'), 'aria-describedby', 'description')

        // Beside the target as the step says; without one, in the middle.
        if (this.#target) {
            content.dataset.side = step.dataset.side ?? 'bottom'
            content.dataset.align = step.dataset.align ?? 'center'
        } else {
            delete content.dataset.side
            delete content.dataset.align
        }

        this.spotlight?.toggleAttribute('data-empty', ! this.#target)

        const counter = content.querySelector('[data-slot="tour-counter"]')

        if (counter) counter.textContent = (counter.dataset.format ?? ':current / :total').replace(':current', index + 1).replace(':total', this.steps.length)

        action('previous')?.toggleAttribute('hidden', index === 0)
        action('next')?.toggleAttribute('hidden', last)
        action('finish')?.toggleAttribute('hidden', ! last)
        action(last ? 'finish' : 'next')?.focus({ preventScroll: true })

        this.dispatchEvent(new CustomEvent('tour-change', { bubbles: true, detail: { step: index } }))
    }

    close(finished = false) {
        if (! this.isOpen) return

        const step = this.#step

        this.#point(null)
        this.content.hidePopover()
        this.spotlight?.hidePopover()
        this.#step = -1
        this.#returnFocus?.focus?.({ preventScroll: true })
        this.#returnFocus = null

        this.dispatchEvent(new CustomEvent('tour-close', { bubbles: true, detail: { step, finished } }))
    }

    /**
     * Livewire re-rendered the page: find the step's target again and anchor it, as a
     * morph may have replaced it or reset its style.
     */
    refresh() {
        if (this.isOpen) this.#point(this.#find(this.steps[this.#step]))
    }

    #find(step) {
        return step?.dataset.target ? document.querySelector(step.dataset.target) : null
    }

    // The target keeps its own anchor names (a Popover trigger's, say) beside ours.
    #point(target, { scroll = false } = {}) {
        if (this.#target && this.#target !== target) {
            this.#target.style.setProperty('anchor-name', this.#names(this.#target).join(', ') || null)
            this.#target.removeAttribute('data-tour-target')
        }

        this.#target = target

        if (! target) return

        target.style.setProperty('anchor-name', [...this.#names(target), ANCHOR].join(', '))
        target.setAttribute('data-tour-target', '')

        // At once, so the panel picks its place with the target in view (as Chrome keeps the place that fit last).
        if (scroll) target.scrollIntoView({ block: 'center' })
    }

    #names(element) {
        return (element.style.getPropertyValue('anchor-name') || '').split(',').map(name => name.trim()).filter(name => name && name !== 'none' && name !== ANCHOR)
    }

    #label(part, attribute, suffix) {
        if (! part) return this.content.removeAttribute(attribute)

        part.id = `${this.id}-${suffix}`
        this.content.setAttribute(attribute, part.id)
    }
}

const open = () => [...document.querySelectorAll('eso-tour')].filter(tour => tour instanceof TourElement && tour.isOpen)

export function register() {
    define('eso-tour', TourElement)

    // Ui::tour('name')->start($step) / ->close().
    window.addEventListener('ui-tour', ({ detail }) => {
        const tour = document.getElementById(detail?.name)

        if (! (tour instanceof TourElement)) return

        detail.open ? tour.start(detail.step ?? 0) : tour.close()
    })

    // Escape closes the tour wherever the focus is, once what the target opened is closed:
    // the browser closes menus (auto popovers) and modal dialogs itself, and components
    // that close their own layer cancel the key.
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || event.defaultPrevented || ! open().length) return
        if (document.querySelector('[popover]:not([popover="manual"]):popover-open, dialog:modal')) return

        event.preventDefault()
        open().forEach(tour => tour.close())
    })

    onLivewire(Livewire => Livewire.hook('morphed', () => open().forEach(tour => tour.refresh())))
}
