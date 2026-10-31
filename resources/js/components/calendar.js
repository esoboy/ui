/*
 * <eso-calendar>
 *
 * shadcn's Calendar (react-day-picker) rendered on the client in the app's locale.
 * Modes: single (.value "Y-m-d" or null), multiple (array of "Y-m-d") and range
 * ({ from, to }). input/change are dispatched from the calendar, so wire:model binds on it.
 */

import { announce, connected, define, disconnected, readValue } from '../support/element.js'
import { clone, readTemplates } from '../support/templates.js'

const pad = number => String(number).padStart(2, '0')

export const toKey = date => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`

export const fromKey = key => {
    const [year, month, day] = String(key).split('-').map(Number)

    return new Date(year, (month || 1) - 1, day || 1)
}

const addDays = (date, days) => new Date(date.getFullYear(), date.getMonth(), date.getDate() + days)
const addMonths = (date, months) => new Date(date.getFullYear(), date.getMonth() + months, 1)
const startOfMonth = date => new Date(date.getFullYear(), date.getMonth(), 1)
const monthIndex = date => date.getFullYear() * 12 + date.getMonth()

// Sets aria-disabled="true", data-today="true", … or removes it.
const flag = (element, name, on) => (on ? element.setAttribute(name, 'true') : element.removeAttribute(name))

export function weekStart(locale) {
    try {
        const info = new Intl.Locale(locale)
        const firstDay = (info.getWeekInfo?.() ?? info.weekInfo)?.firstDay

        return firstDay ? firstDay % 7 : 0
    } catch {
        return 0
    }
}

export class CalendarElement extends HTMLElement {
    #value = null
    #month = null
    #focused = null
    #templates = {}

    connectedCallback() {
        const signal = connected(this)

        // Read before the first render replaces the children; kept when the calendar moves.
        this.#templates = { ...this.#templates, ...readTemplates(this) }
        this.#value = this.#normalize(readValue(this))

        const anchor = this.dataset.defaultMonth
            ? fromKey(`${this.dataset.defaultMonth}-01`)
            : this.#firstSelected() ?? new Date()

        this.#month = startOfMonth(anchor)

        this.addEventListener('click', event => this.#onClick(event), { signal })
        this.addEventListener('change', event => this.#onDropdown(event), { signal })
        this.addEventListener('keydown', event => this.#onKey(event), { signal })

        this.render()
    }

    disconnectedCallback() {
        disconnected(this)
    }

    get mode() {
        return this.dataset.mode ?? 'single'
    }

    get locale() {
        return this.dataset.locale || this.ownerDocument.documentElement.lang || undefined
    }

    get month() {
        return toKey(this.#month).slice(0, 7)
    }

    get value() {
        if (this.mode === 'multiple') return [...this.#value]
        if (this.mode === 'range') return { ...this.#value }

        return this.#value
    }

    set value(value) {
        this.#value = this.#normalize(value)

        const first = this.#firstSelected()

        if (first && ! this.#isMonthShown(first)) this.#month = startOfMonth(first)

        this.render()
    }

    goTo(month) {
        const target = startOfMonth(month)

        if (! this.#canShow(target)) return

        this.#month = target
        this.render()
    }

    select(key) {
        const day = fromKey(key)

        if (this.#isDisabled(day)) return

        if (this.mode === 'multiple') {
            this.#value = this.#value.includes(key) ? this.#value.filter(selected => selected !== key) : [...this.#value, key].sort()
        } else if (this.mode === 'range') {
            const { from, to } = this.#value

            if (! from || to) this.#value = { from: key, to: null }
            else if (key < from) this.#value = { from: key, to: from }
            else this.#value = { from, to: key }
        } else {
            this.#value = key
        }

        this.#focused = key
        this.render()
        announce(this)
    }

    render() {
        const parts = this.#templates

        if (! parts.months) return

        const months = Math.max(1, Number(this.dataset.numberOfMonths) || 1)
        const first = this.#month
        const restoreFocus = this.contains(this.ownerDocument.activeElement)
        const root = clone(parts.months)
        const nav = clone(parts.nav)

        flag(nav.querySelector('[data-calendar-previous]'), 'aria-disabled', ! this.#canShow(addMonths(first, -1)))
        flag(nav.querySelector('[data-calendar-next]'), 'aria-disabled', ! this.#canShow(addMonths(first, months)))

        root.append(nav, ...Array.from({ length: months }, (_, offset) => this.#renderMonth(addMonths(first, offset))))
        this.replaceChildren(root)

        if (restoreFocus) this.#focusDay(this.#focused ?? this.#defaultFocus())
    }

    #renderMonth(month) {
        const parts = this.#templates
        const locale = this.locale
        const start = this.dataset.weekStartsOn ? Number(this.dataset.weekStartsOn) : weekStart(locale)
        const showOutside = this.dataset.showOutsideDays !== 'false'
        const today = toKey(new Date())
        const focus = this.#focused ?? this.#defaultFocus()
        const element = clone(parts.month)
        const weekdays = element.querySelector('[data-slot="calendar-weekdays"]')
        const body = element.querySelector('tbody')

        element.querySelector('[data-slot="calendar-caption"]').append(this.#renderCaption(month))

        // Weekday headers, from a known Sunday.
        for (let index = 0; index < 7; index++) {
            const date = new Date(2023, 0, 1 + ((start + index) % 7))
            const short = date.toLocaleDateString(locale, { weekday: 'short' })
            const weekday = clone(parts.weekday)

            weekday.setAttribute('aria-label', date.toLocaleDateString(locale, { weekday: 'long' }))
            weekday.textContent = short.length > 2 && /^[a-z]/i.test(short) ? short.slice(0, 2) : short
            weekdays.append(weekday)
        }

        const firstDay = addDays(month, -((month.getDay() - start + 7) % 7))

        for (let week = 0, day = firstDay; week < 6; week++) {
            if (week > 0 && day.getMonth() !== month.getMonth()) break

            const row = clone(parts.week)

            for (let index = 0; index < 7; index++, day = addDays(day, 1)) {
                const key = toKey(day)
                const outside = day.getMonth() !== month.getMonth()

                if (outside && ! showOutside) {
                    row.append(clone(parts['day-hidden']))
                    continue
                }

                const state = this.#dayState(key)
                const disabled = this.#isDisabled(day)
                const cell = clone(parts.day)
                const button = cell.querySelector('button')

                // The day's state goes on the cell and its button alike, for the CSS.
                for (const element of [cell, button]) {
                    flag(element, 'data-today', key === today)
                    flag(element, 'data-outside', outside)
                    flag(element, 'data-selected', state.selected)
                    flag(element, 'data-selected-single', state.single)
                    flag(element, 'data-range-start', state.start)
                    flag(element, 'data-range-end', state.end)
                    flag(element, 'data-range-middle', state.middle)
                    flag(element, 'data-disabled', disabled)
                }

                button.dataset.day = key
                button.setAttribute('aria-label', day.toLocaleDateString(locale, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
                flag(button, 'aria-pressed', state.selected)
                flag(button, 'aria-disabled', disabled)
                button.tabIndex = key === focus ? 0 : -1
                button.textContent = day.getDate()
                row.append(cell)
            }

            body.append(row)
        }

        return element
    }

    #renderCaption(month) {
        const parts = this.#templates
        const locale = this.locale

        if (this.dataset.captionLayout !== 'dropdown') {
            return Object.assign(clone(parts.caption), { textContent: month.toLocaleDateString(locale, { month: 'long', year: 'numeric' }) })
        }

        const [startYear, endYear] = this.#yearRange()
        const dropdown = (kind, options, text) => {
            const element = clone(parts[`dropdown-${kind}`])
            const select = element.querySelector('select')

            for (const { value, label, selected, disabled = false } of options) {
                const option = clone(parts.option)

                option.value = value
                option.textContent = label
                option.toggleAttribute('selected', selected)
                option.toggleAttribute('disabled', disabled)
                select.append(option)
            }

            element.querySelector('[data-slot="calendar-caption-label"]').prepend(text)

            return element
        }

        const months = Array.from({ length: 12 }, (_, index) => ({
            value: index,
            label: new Date(2023, index, 1).toLocaleDateString(locale, { month: 'short' }),
            selected: index === month.getMonth(),
            disabled: ! this.#canShow(new Date(month.getFullYear(), index, 1)),
        }))
        const years = Array.from({ length: endYear - startYear + 1 }, (_, index) => ({
            value: startYear + index,
            label: new Date(startYear + index, 0, 1).toLocaleDateString(locale, { year: 'numeric' }),
            selected: startYear + index === month.getFullYear(),
        }))
        const dropdowns = clone(parts.dropdowns)

        dropdowns.append(
            dropdown('month', months, month.toLocaleDateString(locale, { month: 'short' })),
            dropdown('year', years, month.toLocaleDateString(locale, { year: 'numeric' })),
        )

        return dropdowns
    }

    #dayState(key) {
        if (this.mode === 'multiple') {
            const selected = this.#value.includes(key)

            return { selected, single: selected }
        }

        if (this.mode === 'range') {
            const { from, to } = this.#value
            const start = key === from
            const end = key === (to ?? from)
            const middle = Boolean(from && to && key > from && key < to)

            return { selected: start || end || middle, start, end, middle }
        }

        return { selected: key === this.#value, single: key === this.#value }
    }

    #normalize(value) {
        if (this.mode === 'multiple') return (Array.isArray(value) ? value : []).map(String).sort()
        if (this.mode === 'range') return { from: value?.from ?? null, to: value?.to ?? null }

        return value === null || value === undefined || value === '' ? null : String(value)
    }

    #firstSelected() {
        const key = this.mode === 'multiple' ? this.#value[0] : this.mode === 'range' ? this.#value.from : this.#value

        return key ? fromKey(key) : null
    }

    #isMonthShown(date) {
        const offset = monthIndex(date) - monthIndex(this.#month)

        return offset >= 0 && offset < Math.max(1, Number(this.dataset.numberOfMonths) || 1)
    }

    #isDisabled(date) {
        const key = toKey(date)
        const { min, max } = this.dataset
        const disabled = this.dataset.disabledDates ? JSON.parse(this.dataset.disabledDates) : []

        return Boolean((min && key < min) || (max && key > max) || disabled.includes(key))
    }

    #yearRange() {
        const current = this.#month.getFullYear()
        const start = this.dataset.startMonth ? Number(this.dataset.startMonth.slice(0, 4)) : this.dataset.min ? Number(this.dataset.min.slice(0, 4)) : current - 100
        const end = this.dataset.endMonth ? Number(this.dataset.endMonth.slice(0, 4)) : this.dataset.max ? Number(this.dataset.max.slice(0, 4)) : current + 10

        return [Math.min(start, current), Math.max(end, current)]
    }

    #canShow(month) {
        const { min, max, startMonth, endMonth } = this.dataset
        const index = monthIndex(month)
        const lower = startMonth ?? min?.slice(0, 7)
        const upper = endMonth ?? max?.slice(0, 7)

        return ! (lower && index < monthIndex(fromKey(`${lower}-01`))) && ! (upper && index > monthIndex(fromKey(`${upper}-01`)))
    }

    #defaultFocus() {
        const selected = this.#firstSelected()

        if (selected && this.#isMonthShown(selected)) return toKey(selected)

        const today = new Date()

        return this.#isMonthShown(today) ? toKey(today) : toKey(this.#month)
    }

    #focusDay(key) {
        this.querySelector(`button[data-day="${key}"]`)?.focus()
    }

    #onClick(event) {
        const button = event.target.closest('button')

        if (! button || button.getAttribute('aria-disabled') === 'true') return

        if (button.hasAttribute('data-calendar-previous')) return this.goTo(addMonths(this.#month, -1))
        if (button.hasAttribute('data-calendar-next')) return this.goTo(addMonths(this.#month, 1))
        if (button.dataset.day) this.select(button.dataset.day)
    }

    #onDropdown(event) {
        const select = event.target

        if (select === this) return

        event.stopPropagation()

        if (select.matches('[data-calendar-month]')) this.goTo(new Date(this.#month.getFullYear(), Number(select.value), 1))
        if (select.matches('[data-calendar-year]')) this.goTo(new Date(Number(select.value), this.#month.getMonth(), 1))
    }

    #onKey(event) {
        const key = event.target.dataset?.day

        if (! key) return

        const day = fromKey(key)
        const moves = {
            ArrowLeft: () => addDays(day, -1),
            ArrowRight: () => addDays(day, 1),
            ArrowUp: () => addDays(day, -7),
            ArrowDown: () => addDays(day, 7),
            Home: () => addDays(day, -((day.getDay() - (this.dataset.weekStartsOn ? Number(this.dataset.weekStartsOn) : weekStart(this.locale)) + 7) % 7)),
            End: () => addDays(day, 6 - ((day.getDay() - (this.dataset.weekStartsOn ? Number(this.dataset.weekStartsOn) : weekStart(this.locale)) + 7) % 7)),
            PageUp: () => new Date(day.getFullYear(), day.getMonth() - 1, day.getDate()),
            PageDown: () => new Date(day.getFullYear(), day.getMonth() + 1, day.getDate()),
        }

        if (! moves[event.key]) return

        event.preventDefault()

        const target = moves[event.key]()

        this.#focused = toKey(target)

        if (! this.#isMonthShown(target)) {
            this.#month = startOfMonth(target)
            this.render()
        }

        this.querySelectorAll('button[data-day]').forEach(button => (button.tabIndex = button.dataset.day === this.#focused ? 0 : -1))
        this.#focusDay(this.#focused)
    }
}

export function register() {
    define('eso-calendar', CalendarElement)
}
