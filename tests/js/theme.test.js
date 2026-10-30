import { afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { register as registerTheme, setTheme, theme } from '../../resources/js/components/theme.js'

const system = { matches: false, listeners: [], addEventListener(type, listener) { this.listeners.push(listener) } }

beforeAll(() => {
    window.matchMedia = () => system
    registerTheme()
})

beforeEach(() => {
    document.documentElement.setAttribute('data-theme-scope', 'admin')
})

afterEach(() => {
    document.body.innerHTML = ''
    document.documentElement.removeAttribute('data-theme')
    document.documentElement.removeAttribute('data-theme-switching')
    document.cookie = 'ui_theme=; path=/; max-age=0'
    system.matches = false
})

const saved = () => JSON.parse(decodeURIComponent(document.cookie.split('; ').find(entry => entry.startsWith('ui_theme=')).slice(9)))
const changeSystem = matches => {
    system.matches = matches
    system.listeners.forEach(listener => listener())
}

describe('choosing', () => {
    it('puts the choice on <html> and in the cookie under the app\'s scope', () => {
        document.cookie = `ui_theme=${encodeURIComponent(JSON.stringify({ shop: 'light' }))}; path=/`
        document.body.innerHTML = '<button data-theme-value="dark">Dark</button>'

        document.querySelector('button').click()

        expect(theme()).toBe('dark')
        expect(document.documentElement.dataset.theme).toBe('dark')
        expect(saved()).toEqual({ shop: 'light', admin: 'dark' })
    })

    it('goes back to the system without an attribute or cookie entry', () => {
        setTheme('dark')
        setTheme('system')

        expect(document.documentElement.hasAttribute('data-theme')).toBe(false)
        expect(saved()).toEqual({})
    })

    it('ignores anything but light, dark and system', () => {
        setTheme('blue')

        expect(theme()).toBe('system')
    })

    it('switches without transitions', () => {
        setTheme('dark')

        expect(document.documentElement.hasAttribute('data-theme-switching')).toBe(true)
    })
})

describe('controls', () => {
    it('presses the button of the current choice', () => {
        document.body.innerHTML = ['light', 'dark', 'system'].map(value => `<button data-theme-value="${value}"></button>`).join('')

        document.querySelector('[data-theme-value="light"]').click()

        expect([...document.querySelectorAll('button')].map(button => button.getAttribute('aria-pressed'))).toEqual(['true', 'false', 'false'])
    })

    it('marks menu items as checked instead of pressed', () => {
        document.body.innerHTML = '<div role="menuitemradio" data-theme-value="dark"></div>'

        setTheme('dark')

        expect(document.querySelector('[role]').getAttribute('aria-checked')).toBe('true')
        expect(document.querySelector('[role]').hasAttribute('aria-pressed')).toBe(false)
    })

    it('toggles between light and dark from what is on screen', () => {
        document.body.innerHTML = '<button data-theme-value="toggle"></button>'
        changeSystem(true)

        expect(document.querySelector('button').getAttribute('aria-pressed')).toBe('true')

        document.querySelector('button').click()
        expect(theme()).toBe('light')

        document.querySelector('button').click()
        expect(theme()).toBe('dark')
    })

    it('acts on radio inputs, even inside a group that keeps their events', () => {
        document.body.innerHTML = `
            <div data-group>
                <label><input type="radio" name="theme" data-theme-value="light"></label>
                <label><input type="radio" name="theme" data-theme-value="dark"></label>
            </div>`
        document.querySelector('[data-group]').addEventListener('change', event => event.stopPropagation())

        document.querySelector('[data-theme-value="dark"]').click()

        expect(theme()).toBe('dark')

        setTheme('light')
        expect(document.querySelector('[data-theme-value="light"]').checked).toBe(true)
        expect(document.querySelector('[data-theme-value="dark"]').checked).toBe(false)
    })

    it('treats a Switch as dark when on and light when off', () => {
        document.body.innerHTML = '<input type="checkbox" role="switch" data-theme-value="toggle">'
        const toggle = document.querySelector('input')

        toggle.click()
        expect(theme()).toBe('dark')

        toggle.click()
        expect(theme()).toBe('light')
    })

    it('treats a Toggle for one theme as that theme when on and the system\'s when off', () => {
        document.body.innerHTML = '<input type="checkbox" data-theme-value="dark">'
        const toggle = document.querySelector('input')

        toggle.click()
        expect(theme()).toBe('dark')

        toggle.click()
        expect(theme()).toBe('system')
    })
})

describe('keeping in step', () => {
    it('follows the system on "toggle" controls while the choice is system', () => {
        document.body.innerHTML = '<button data-theme-value="toggle"></button>'

        changeSystem(true)
        expect(document.querySelector('button').getAttribute('aria-pressed')).toBe('true')

        changeSystem(false)
        expect(document.querySelector('button').getAttribute('aria-pressed')).toBe('false')
    })

    it('restores the saved choice after wire:navigate brings an older page', () => {
        setTheme('dark')
        document.documentElement.removeAttribute('data-theme')

        document.dispatchEvent(new Event('livewire:navigated'))

        expect(theme()).toBe('dark')
    })
})
