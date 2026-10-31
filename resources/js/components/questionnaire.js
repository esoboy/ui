/*
 * Questionnaires: the behaviour of shadcn's Questionnaire (@shadcn/react) on a server
 * rendered <form data-slot="questionnaire">. One enabled item shows at a time; the
 * others are hidden and inert. Next and Enter need an answer (or a skip, for optional
 * items); Previous, Skip and Submit show only where they apply. Ctrl/Cmd+Enter goes
 * on, the arrows move between answers and items, and letter or number shortcuts
 * (data-shortcuts) pick a choice. Submitting jumps to the first unanswered item.
 *
 * The active item lives here, not in the markup, and is put back after every morph.
 */

const FORM = 'form[data-slot="questionnaire"]'
const ITEM = '[data-slot="questionnaire-item"]'
const CHOICE = '[data-slot="questionnaire-choice-input"]'
const TEXT = '[data-slot="questionnaire-input"]'
const TEXT_TYPES = ['email', 'password', 'search', 'tel', 'text', 'url']

let ids = 0

export function register() {
    const states = new WeakMap()
    const queued = new Set()

    const state = form => {
        if (! states.has(form)) states.set(form, { active: form.dataset.defaultItem || null, attempted: new Set(), skipped: new Set() })

        return states.get(form)
    }

    const own = (form, selector) => [...form.querySelectorAll(selector)].filter(element => element.closest(FORM) === form)
    const itemsOf = form => own(form, ITEM).filter(item => ! item.disabled)
    const ownIn = (item, selector) => [...item.querySelectorAll(selector)].filter(element => element.closest(ITEM) === item)
    const answersOf = item => ownIn(item, `${CHOICE}, ${TEXT}`).filter(control => ! control.disabled)
    const isText = element => element instanceof window.HTMLTextAreaElement || (element instanceof window.HTMLInputElement && ! ['button', 'checkbox', 'radio', 'reset', 'submit'].includes(element.type)) || element?.isContentEditable

    const answered = item => answersOf(item).some(control => (control.matches(CHOICE) ? control.checked : control.value.trim() !== ''))
    const required = item => item.hasAttribute('data-required')

    const status = (form, item) => (answered(item) ? 'answered' : state(form).skipped.has(item.dataset.name) ? 'skipped' : 'unanswered')
    const settled = (form, item) => status(form, item) === 'answered' || (status(form, item) === 'skipped' && ! required(item))
    const invalid = (form, item) => state(form).attempted.has(item.dataset.name) && ! settled(form, item)

    const activeItem = form => {
        const items = itemsOf(form)
        const current = state(form).active

        return items.find(item => item.dataset.name === current) ?? items[0] ?? null
    }

    // Writes only real changes, so syncing never feeds the observer that triggers it.
    const set = (element, name, value) => {
        if (value === null || value === false) return element.hasAttribute(name) && element.removeAttribute(name)

        const wanted = value === true ? '' : String(value)

        if (element.getAttribute(name) !== wanted) element.setAttribute(name, wanted)
    }

    const shortcutKeys = mode => (mode === 'letters' ? [...'ABCDEFGHIJKLMNOPQRSTUVWXYZ'] : mode === 'numbers' ? [...'123456789'] : [])

    // Puts the markup in line with the state: visibility, validity, progress, actions, shortcuts.
    const sync = form => {
        const items = itemsOf(form)
        const active = activeItem(form)
        const index = items.indexOf(active)
        const total = items.length
        const last = index === total - 1
        const keys = shortcutKeys(form.dataset.shortcuts)

        state(form).active = active?.dataset.name ?? null

        for (const item of own(form, ITEM)) {
            const isActive = item === active
            const bad = ! item.disabled && invalid(form, item)
            const error = ownIn(item, '[data-slot="questionnaire-error"]')[0]
            const described = ownIn(item, '[data-slot="questionnaire-description"]')

            set(item, 'data-active', isActive)
            set(item, 'hidden', ! isActive)
            set(item, 'inert', ! isActive)
            set(item, 'aria-invalid', bad ? 'true' : null)

            for (const element of [...described, error].filter(Boolean)) if (! element.id) element.id = `ui-questionnaire-${++ids}`
            set(item, 'aria-describedby', [...described, ...(bad && error ? [error] : [])].map(element => element.id).join(' ') || null)

            if (error) {
                set(error, 'hidden', ! bad)
                set(error, 'role', bad ? 'alert' : null)
            }

            for (const control of answersOf(item)) set(control, 'aria-invalid', bad ? 'true' : null)

            ownIn(item, CHOICE).filter(input => ! input.disabled).forEach((input, position) => {
                const choice = input.closest('[data-slot="questionnaire-choice"]')
                const badge = choice?.querySelector('[data-slot="questionnaire-choice-shortcut"]')
                const key = keys[position] ?? null

                if (choice) set(choice, 'data-shortcut', key)
                set(input, 'aria-keyshortcuts', key)

                if (badge) {
                    if (badge.textContent !== (key ?? '')) badge.textContent = key ?? ''
                    set(badge, 'hidden', key === null)
                }
            })
        }

        for (const progress of own(form, '[data-slot="questionnaire-progress"]')) {
            const text = total ? (progress.dataset.template ?? '').replace(':current', index + 1).replace(':total', total) : ''

            set(progress, 'aria-valuemin', total ? 1 : null)
            set(progress, 'aria-valuemax', total || null)
            set(progress, 'aria-valuenow', total ? index + 1 : null)
            set(progress, 'aria-valuetext', text || null)
            if (progress.dataset.template && progress.textContent !== text) progress.textContent = text
        }

        const visible = {
            previous: total > 1 && index > 0,
            skip: !! active && ! required(active),
            next: total > 1 && ! last,
            submit: total > 0 && last,
        }

        for (const button of own(form, '[data-questionnaire]')) {
            const shown = visible[button.dataset.questionnaire] ?? true

            set(button, 'hidden', ! shown)
            set(button, 'inert', ! shown)
            set(button, 'data-visible', shown)
            set(button, 'data-status', active ? status(form, active) : null)
        }
    }

    const focusItem = (form, invalidOnly = false) => {
        const item = activeItem(form)

        if (! item) return

        const answers = answersOf(item)
        const target = invalidOnly
            ? answers.find(control => control.matches(TEXT) && control.value) ?? answers[0]
            : null

        ;(target ?? item).focus()
    }

    const go = (form, name, { invalid: toInvalid = false } = {}) => {
        state(form).active = name
        sync(form)
        focusItem(form, toInvalid)
    }

    const validate = (form, item) => {
        state(form).attempted.add(item.dataset.name)
        sync(form)

        if (settled(form, item)) return true

        focusItem(form, true)

        return false
    }

    const move = (form, step) => {
        const items = itemsOf(form)
        const next = items[items.indexOf(activeItem(form)) + step]

        if (next) go(form, next.dataset.name)
    }

    const goOn = form => {
        const item = activeItem(form)

        if (! item || ! validate(form, item)) return

        const items = itemsOf(form)

        items.indexOf(item) === items.length - 1 ? form.requestSubmit() : move(form, 1)
    }

    const skip = form => {
        const item = activeItem(form)

        if (! item || required(item)) return

        for (const control of answersOf(item)) {
            if (control.matches(CHOICE) ? ! control.checked : control.value === '') continue

            control.matches(CHOICE) ? (control.checked = false) : (control.value = '')
            control.dispatchEvent(new Event('input', { bubbles: true }))
            control.dispatchEvent(new Event('change', { bubbles: true }))
        }

        state(form).skipped.add(item.dataset.name)

        const items = itemsOf(form)

        items.indexOf(item) === items.length - 1 ? queueMicrotask(() => form.requestSubmit()) : move(form, 1)
    }

    // --- events ---

    document.addEventListener('click', event => {
        const button = event.target.closest?.('[data-questionnaire]')
        const form = button?.closest(FORM)

        if (! form || button.disabled) return

        const action = button.dataset.questionnaire

        if (action === 'previous') move(form, -1)
        if (action === 'next') goOn(form)
        if (action === 'skip') skip(form)
    })

    // Answering clears a skip; one answer of a single item replaces the other kind.
    let replacing = false

    document.addEventListener('input', event => {
        const control = event.target
        const item = control.closest?.(ITEM)
        const form = item?.closest(FORM)

        if (! form || ! control.matches(`${CHOICE}, ${TEXT}`)) return

        state(form).skipped.delete(item.dataset.name)

        if (! item.hasAttribute('data-multiple') && ! replacing) {
            replacing = true
            if (control.matches(TEXT) && control.value.trim() !== '') {
                for (const radio of ownIn(item, CHOICE).filter(radio => radio.checked)) {
                    radio.checked = false
                    radio.closest('eso-radio-group')?.announce?.('input')
                    radio.closest('eso-radio-group')?.announce?.('change')
                }
            }

            if (control.matches(CHOICE) && control.checked) {
                for (const text of ownIn(item, TEXT).filter(text => text.value !== '')) {
                    text.value = ''
                    text.dispatchEvent(new Event('input', { bubbles: true }))
                }
            }

            replacing = false
        }

        sync(form)
    })

    document.addEventListener('change', event => {
        const form = event.target.closest?.(FORM)

        if (form && event.target.closest(ITEM)) sync(form)
    })

    // Submitting runs before Livewire's wire:submit, and stops it while an item needs an answer.
    document.addEventListener('submit', event => {
        const form = event.target

        if (! form.matches?.(FORM)) return

        const items = itemsOf(form)

        items.forEach(item => state(form).attempted.add(item.dataset.name))

        const first = items.find(item => ! settled(form, item))

        if (! first) return

        event.preventDefault()
        event.stopImmediatePropagation()
        go(form, first.dataset.name, { invalid: true })
    }, true)

    document.addEventListener('reset', event => {
        const form = event.target

        if (! form.matches?.(FORM)) return

        states.delete(form)
        queueMicrotask(() => sync(form))
    })

    document.addEventListener('keydown', event => {
        const form = event.target.closest?.(FORM)
        const item = form && activeItem(form)

        if (! item || event.defaultPrevented || event.isComposing) return

        const target = event.target

        if (event.key === 'Enter' && (event.metaKey || event.ctrlKey) && ! event.altKey && ! event.shiftKey) {
            event.preventDefault()
            if (! event.repeat) goOn(form)

            return
        }

        if (event.metaKey || event.ctrlKey || event.altKey) return

        const answers = answersOf(item)
        const textual = isText(target)
        const isRadio = target.type === 'radio'

        // Up and down walk the answers; radios keep their native behaviour among themselves.
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            const index = answers.indexOf(target)

            if (textual && ! (TEXT_TYPES.includes(target.type) && target.value.trim() === '')) return
            if (index < 0 && target !== item) return

            const next = index < 0
                ? answers.find(control => control.checked) ?? (event.key === 'ArrowDown' ? answers[0] : answers.at(-1))
                : answers[(index + (event.key === 'ArrowDown' ? 1 : -1) + answers.length) % answers.length]

            if (! next || next === target || (index >= 0 && isRadio && next.type === 'radio')) return

            event.preventDefault()
            next.focus()
            if (next.type === 'radio') next.click()

            return
        }

        if ((event.key === 'ArrowLeft' || event.key === 'ArrowRight') && ! textual && ! isRadio) {
            event.preventDefault()
            if (event.repeat) return

            event.key === 'ArrowLeft' ? move(form, -1) : status(form, item) !== 'unanswered' && (validate(form, item) && move(form, 1))

            return
        }

        if (event.key === 'Enter') {
            if (! answers.includes(target)) return

            event.preventDefault()
            if (! event.repeat && answered(item)) goOn(form)

            return
        }

        const keys = shortcutKeys(form.dataset.shortcuts)
        const key = form.dataset.shortcuts === 'letters' ? event.key.toUpperCase() : event.key

        if (! keys.includes(key) || textual) return

        const choice = ownIn(item, CHOICE).filter(input => ! input.disabled)[keys.indexOf(key)]

        if (! choice) return

        event.preventDefault()
        if (event.repeat) return

        choice.focus()
        choice.click()
    })

    // --- keeping forms in sync with morphs and new forms ---

    const flush = () => {
        queued.forEach(form => form.isConnected && sync(form))
        queued.clear()
    }

    const queue = form => {
        if (! queued.size) queueMicrotask(flush)
        queued.add(form)
    }

    new window.MutationObserver(records => {
        for (const record of records) {
            const form = record.target.closest?.(FORM)

            if (form) queue(form)

            for (const node of record.addedNodes) {
                if (node.matches?.(FORM)) queue(node)
                node.querySelectorAll?.(FORM).forEach(queue)
            }
        }
    }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'hidden', 'data-required'] })

    document.querySelectorAll(FORM).forEach(queue)
}
