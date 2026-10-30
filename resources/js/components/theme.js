/*
 * Theme switching from any control: data-theme-value="light", "dark" or "system" picks
 * that theme, "toggle" switches between light and dark. Buttons and menu items act on
 * click; Toggles, Switches, Toggle Group items and menu radio items, which are native
 * inputs, act on change. Every control shows the current choice: inputs are checked,
 * other elements get aria-pressed (aria-checked for menu items and other checkable roles).
 *
 * The choice goes on <html> as data-theme (none for system) and into the ui_theme cookie
 * under the app's scope, <html @uiTheme('admin')>, so the server renders the next page in it.
 */

import { onLivewire } from '../support/livewire.js'

const COOKIE = 'ui_theme'
const CONTROL = '[data-theme-value]'
const CHECKABLE = ['checkbox', 'menuitemcheckbox', 'menuitemradio', 'option', 'radio', 'switch']

let system = null

const scope = () => document.documentElement.dataset.themeScope || 'default'

function saved() {
    const cookie = document.cookie.split('; ').find(entry => entry.startsWith(`${COOKIE}=`))

    try {
        const choices = cookie ? JSON.parse(decodeURIComponent(cookie.slice(COOKIE.length + 1))) : {}

        return choices && typeof choices === 'object' ? choices : {}
    } catch {
        return {}
    }
}

function save(choice) {
    const choices = { ...saved(), [scope()]: choice }

    if (choice === 'system') delete choices[scope()]

    document.cookie = `${COOKIE}=${encodeURIComponent(JSON.stringify(choices))}; path=/; max-age=31536000; samesite=lax`
}

/** The app's choice: light, dark or system. */
export function theme() {
    return document.documentElement.dataset.theme ?? 'system'
}

/** The scheme on screen: the choice, or the system's for system. */
const scheme = () => (theme() === 'system' ? (system?.matches ? 'dark' : 'light') : theme())

function apply(choice) {
    const root = document.documentElement

    if (theme() !== choice) {
        // Every color changes at once instead of transitioning; reading a style applies it first.
        root.setAttribute('data-theme-switching', '')
        choice === 'system' ? root.removeAttribute('data-theme') : root.setAttribute('data-theme', choice)
        window.getComputedStyle(root).colorScheme
        window.setTimeout(() => root.removeAttribute('data-theme-switching'))
    }

    sync()
}

/** Picks a theme for this app: light, dark or system. */
export function setTheme(choice) {
    if (! ['light', 'dark', 'system'].includes(choice)) return

    save(choice)
    apply(choice)
}

function sync() {
    const choice = theme()
    const dark = scheme() === 'dark'

    for (const control of document.querySelectorAll(CONTROL)) {
        const on = control.dataset.themeValue === 'toggle' ? dark : control.dataset.themeValue === choice

        if (control instanceof HTMLInputElement) {
            if (control.checked !== on) control.checked = on
        } else {
            control.setAttribute(CHECKABLE.includes(control.getAttribute('role')) ? 'aria-checked' : 'aria-pressed', String(on))
        }
    }
}

export function register() {
    system = window.matchMedia('(prefers-color-scheme: dark)')

    document.addEventListener('click', event => {
        const control = event.target.closest?.(CONTROL)

        // Inputs act on change; a click on their label reaches them as one.
        if (! control || control instanceof HTMLInputElement) return

        const value = control.dataset.themeValue

        setTheme(value === 'toggle' ? (scheme() === 'dark' ? 'light' : 'dark') : value)
    })

    // Captured, as groups (Toggle Group, menu radio groups) keep their inputs' events to themselves.
    document.addEventListener('change', ({ target }) => {
        if (! (target instanceof HTMLInputElement) || ! target.matches(CONTROL)) return

        const value = target.dataset.themeValue

        if (target.type === 'checkbox') {
            // A Toggle or Switch: on is dark (or its theme), off is light (or the system's).
            setTheme(value === 'toggle' ? (target.checked ? 'dark' : 'light') : target.checked ? value : 'system')
        } else if (target.checked) {
            setTheme(value)
        }
    }, true)

    // A system change moves "toggle" controls when the choice is system.
    system.addEventListener('change', sync)

    // Livewire re-renders controls from the server, and wire:navigate may restore a page
    // from before the last change: the cookie has the choice.
    onLivewire(Livewire => Livewire.hook('morphed', sync))
    document.addEventListener('livewire:navigated', () => apply(saved()[scope()] ?? 'system'))

    sync()
}
