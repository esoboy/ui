import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerTabs } from '../../resources/js/components/tabs.js'

beforeAll(() => registerTabs())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(attributes = '') {
    document.body.innerHTML = `
        <eso-tabs data-orientation="horizontal" ${attributes}>
            <div role="tablist">
                <button role="tab" data-slot="tabs-trigger" data-value="account">Account</button>
                <button role="tab" data-slot="tabs-trigger" data-value="billing" disabled>Billing</button>
                <button role="tab" data-slot="tabs-trigger" data-value="password">Password</button>
            </div>
            <div role="tabpanel" data-slot="tabs-content" data-value="account" hidden>A</div>
            <div role="tabpanel" data-slot="tabs-content" data-value="password" hidden>P</div>
        </eso-tabs>`

    return document.querySelector('eso-tabs')
}

const trigger = (tabs, value) => tabs.querySelector(`[data-slot="tabs-trigger"][data-value="${value}"]`)
const panel = (tabs, value) => tabs.querySelector(`[data-slot="tabs-content"][data-value="${value}"]`)
const press = key => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true }))

describe('value', () => {
    it('activates the first enabled tab without a value, like Base UI', () => {
        const tabs = mount()

        expect(tabs.value).toBe('account')
        expect(trigger(tabs, 'account').hasAttribute('data-active')).toBe(true)
        expect(panel(tabs, 'account').hidden).toBe(false)
        expect(panel(tabs, 'password').hidden).toBe(true)
    })

    it('starts from data-value and links triggers to panels', () => {
        const tabs = mount('data-value="password"')
        const tab = trigger(tabs, 'password')

        expect(tab.getAttribute('aria-selected')).toBe('true')
        expect(tab.tabIndex).toBe(0)
        expect(trigger(tabs, 'account').tabIndex).toBe(-1)
        expect(tab.getAttribute('aria-controls')).toBe(panel(tabs, 'password').id)
        expect(panel(tabs, 'password').getAttribute('aria-labelledby')).toBe(tab.id)
    })

    it('takes values set by Livewire', () => {
        const tabs = mount()

        tabs.value = 'password'

        expect(panel(tabs, 'password').hidden).toBe(false)
        expect(panel(tabs, 'account').hidden).toBe(true)
    })

    it('restores its state after a morph resets it', async () => {
        const tabs = mount('data-value="password"')

        panel(tabs, 'password').hidden = true
        trigger(tabs, 'password').removeAttribute('data-active')

        await vi.waitFor(() => expect(panel(tabs, 'password').hidden).toBe(false))
        expect(trigger(tabs, 'password').hasAttribute('data-active')).toBe(true)
    })
})

describe('interaction', () => {
    it('activates on click and announces from the tabs', () => {
        const tabs = mount()
        const seen = []

        tabs.addEventListener('input', event => event.target === tabs && seen.push(tabs.value))
        trigger(tabs, 'password').click()
        trigger(tabs, 'password').click()

        expect(tabs.value).toBe('password')
        expect(seen).toEqual(['password'])
    })

    it('ignores disabled tabs', () => {
        const tabs = mount()

        trigger(tabs, 'billing').click()

        expect(tabs.value).toBe('account')
    })

    it('moves focus with the arrows, skipping disabled tabs and wrapping', () => {
        const tabs = mount()

        trigger(tabs, 'account').focus()
        press('ArrowRight')
        expect(document.activeElement.dataset.value).toBe('password')

        press('ArrowRight')
        expect(document.activeElement.dataset.value).toBe('account')

        press('End')
        expect(document.activeElement.dataset.value).toBe('password')
        expect(tabs.value).toBe('account')
    })

    it('uses up and down when vertical', () => {
        const tabs = mount()

        tabs.dataset.orientation = 'vertical'
        trigger(tabs, 'account').focus()
        press('ArrowDown')

        expect(document.activeElement.dataset.value).toBe('password')
    })

    it('leaves nested tabs alone', () => {
        const outer = mount()

        panel(outer, 'account').innerHTML = '<eso-tabs><button data-slot="tabs-trigger" data-value="inner">Inner</button></eso-tabs>'
        outer.querySelector('[data-value="inner"]').click()

        expect(outer.value).toBe('account')
        expect(outer.triggers).toHaveLength(3)
    })
})
