/*
 * <eso-date-picker>
 *
 * A popover trigger showing the chosen date(s) and a <eso-calendar> inside. .value
 * proxies the calendar and input/change are dispatched from the picker itself, so
 * wire:model binds on it. With data-time the footer's time inputs join the dates:
 * "Y-m-d H:i" (H:i:s when data-step is under a minute), or { from, to } of those.
 */

import { announce, connected, define, disconnected } from '../support/element.js'
import { fromKey } from './calendar.js'

const TIME = '[data-date-picker-time]'

let times = 0

export class DatePickerElement extends HTMLElement {
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        // The time inputs sit in wire:ignore, so ids given here stay: their labels need them.
        for (const input of this.querySelectorAll(TIME)) {
            input.id ||= `ui-date-picker-time-${++times}`
            input.closest('[data-slot="field"]')?.querySelector('[data-slot="field-label"]')?.setAttribute('for', input.id)
        }

        const own = event => event.target === this.calendar || event.target.matches?.(TIME)

        this.addEventListener('input', event => own(event) && this.#announce(event, 'input'), { signal })
        this.addEventListener('change', event => own(event) && this.#announce(event, 'change'), { signal })

        // A preset sets the calendar and shows its month.
        this.addEventListener('click', event => {
            const preset = event.target.closest?.('[data-date-picker-preset]')

            if (! preset || ! this.calendar) return

            this.calendar.value = JSON.parse(preset.dataset.datePickerPreset)
            this.render()
            announce(this)
        }, { signal })

        this.render()

        // Livewire morphs reset the trigger to the server's placeholder: put the date back.
        this.#observer = new MutationObserver(() => this.render())
        this.#observer.observe(this, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ['aria-invalid', 'aria-describedby'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get calendar() {
        return this.querySelector('eso-calendar')
    }

    get trigger() {
        return this.querySelector('[data-date-picker-trigger]')
    }

    get time() {
        return this.hasAttribute('data-time')
    }

    get value() {
        const value = this.calendar?.value ?? null

        if (! this.time) return value

        const at = (date, part) => (date ? `${date} ${this.#time(part)}` : null)

        return this.#range ? { from: at(value?.from, 'from'), to: at(value?.to, 'to') } : at(value, 'from')
    }

    set value(value) {
        if (this.time) {
            const split = at => String(at ?? '').split(/[ T]/)
            const [from, fromTime] = split(this.#range ? value?.from : value)
            const [to, toTime] = split(value?.to)

            this.#setTime('from', fromTime)
            this.#setTime('to', toTime)
            value = this.#range ? { from: from || null, to: to || null } : from || null
        }

        if (this.calendar) this.calendar.value = value

        this.render()
    }

    render() {
        const display = this.querySelector('[data-slot="date-picker-value"]')
        const trigger = this.trigger

        // The calendar may not be upgraded yet; its first render triggers ours again.
        if (! display || ! trigger || ! this.calendar?.month) return

        const text = this.#format(this.value)
        const empty = text === ''

        if (display.textContent !== (empty ? this.dataset.placeholder ?? '' : text)) display.textContent = empty ? this.dataset.placeholder ?? '' : text
        if (trigger.dataset.empty !== String(empty)) trigger.dataset.empty = String(empty)

        // The picker carries the field's validation state; screen readers need it on the trigger.
        for (const name of ['aria-invalid', 'aria-describedby']) {
            const value = this.getAttribute(name)

            if (value === null) trigger.hasAttribute(name) && trigger.removeAttribute(name)
            else if (trigger.getAttribute(name) !== value) trigger.setAttribute(name, value)
        }
    }

    get #range() {
        return this.calendar?.dataset.mode === 'range'
    }

    get #seconds() {
        return Number(this.dataset.step) > 0 && Number(this.dataset.step) < 60
    }

    // A time input's value as H:i (or H:i:s), the default time when it is empty.
    #time(part) {
        const [hours = '00', minutes = '00', seconds = '00'] = (this.querySelector(`${TIME}[data-date-picker-time="${part}"]`)?.value || this.dataset.defaultTime || '00:00').split(':')

        return [hours, minutes, ...(this.#seconds ? [seconds] : [])].join(':')
    }

    #setTime(part, time) {
        const input = this.querySelector(`${TIME}[data-date-picker-time="${part}"]`)

        if (input && time && input.value !== time) input.value = time
    }

    #format(value) {
        const locale = this.dataset.locale || undefined

        if (this.time) {
            const at = text => {
                const [date, time = '00:00'] = text.split(/[ T]/)
                const [hours, minutes, seconds] = time.split(':').map(Number)
                const day = fromKey(date)

                day.setHours(hours || 0, minutes || 0, seconds || 0)

                return day
            }
            const timeStyle = this.#seconds ? 'medium' : 'short'

            if (value && typeof value === 'object') return value.from ? [value.from, value.to].filter(Boolean).map(text => at(text).toLocaleString(locale, { dateStyle: 'medium', timeStyle })).join(' - ') : ''

            return value ? at(value).toLocaleString(locale, { dateStyle: 'long', timeStyle }) : ''
        }

        const long = key => fromKey(key).toLocaleDateString(locale, { year: 'numeric', month: 'long', day: 'numeric' })
        const short = key => fromKey(key).toLocaleDateString(locale, { year: 'numeric', month: 'short', day: '2-digit' })

        if (Array.isArray(value)) return value.map(short).join(', ')
        if (value && typeof value === 'object') return value.from ? [value.from, value.to].filter(Boolean).map(short).join(' - ') : ''

        return value ? long(value) : ''
    }

    #announce(event, type) {
        event.stopPropagation()
        this.render()
        this.dispatchEvent(new Event(type, { bubbles: true }))
    }
}

export function register() {
    define('eso-date-picker', DatePickerElement)
}
