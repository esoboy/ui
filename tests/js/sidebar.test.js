import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerSidebar } from '../../resources/js/components/sidebar.js'

beforeAll(() => registerSidebar())

const desktop = window.matchMedia

afterEach(() => {
    document.body.innerHTML = ''
    document.cookie = 'sidebar_state=; max-age=0; path=/'
    window.matchMedia = desktop
})

function mount(state = 'expanded') {
    document.body.innerHTML = `
        <eso-sidebar-provider>
            <div data-slot="sidebar" data-state="${state}" data-collapsible="${state === 'expanded' ? '' : 'icon'}" data-collapsible-mode="icon">
                <div data-slot="sidebar-container" popover="manual">
                    <a href="/">Home</a>
                    <button data-sidebar="rail"></button>
                </div>
            </div>
            <main><button data-sidebar="trigger">Toggle</button></main>
        </eso-sidebar-provider>
        <p>Outside</p>`

    return document.querySelector('eso-sidebar-provider')
}

const sidebar = () => document.querySelector('[data-slot="sidebar"]')
const container = () => document.querySelector('[data-slot="sidebar-container"]')

describe('desktop', () => {
    it('collapses and expands from the trigger and the rail, remembering it in a cookie', () => {
        mount()

        document.querySelector('[data-sidebar="trigger"]').click()
        expect(sidebar().dataset.state).toBe('collapsed')
        expect(sidebar().dataset.collapsible).toBe('icon')
        expect(document.cookie).toContain('sidebar_state=false')

        document.querySelector('[data-sidebar="rail"]').click()
        expect(sidebar().dataset.state).toBe('expanded')
        expect(sidebar().dataset.collapsible).toBe('')
        expect(document.cookie).toContain('sidebar_state=true')
    })

    it('toggles with Ctrl or Cmd+B', () => {
        mount('collapsed')

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'b', metaKey: true, cancelable: true }))
        expect(sidebar().dataset.state).toBe('expanded')

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'b', ctrlKey: true, cancelable: true }))
        expect(sidebar().dataset.state).toBe('collapsed')

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'b', cancelable: true }))
        expect(sidebar().dataset.state).toBe('collapsed')
    })

    it('stops listening once removed', () => {
        mount()
        document.body.innerHTML = ''

        expect(() => window.dispatchEvent(new KeyboardEvent('keydown', { key: 'b', ctrlKey: true }))).not.toThrow()
    })
})

describe('mobile', () => {
    function mountMobile() {
        window.matchMedia = query => ({ media: query, matches: true, addEventListener() {}, removeEventListener() {} })

        return mount()
    }

    it('opens the sheet instead of collapsing, focusing into it', () => {
        const provider = mountMobile()
        const trigger = document.querySelector('[data-sidebar="trigger"]')

        trigger.focus()
        trigger.click()

        expect(provider.openMobile).toBe(true)
        expect(sidebar().dataset.state).toBe('expanded')
        expect(document.activeElement.textContent).toBe('Home')
    })

    it('closes on Escape and returns focus to the trigger', () => {
        const provider = mountMobile()
        const trigger = document.querySelector('[data-sidebar="trigger"]')

        trigger.focus()
        trigger.click()
        document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }))

        expect(provider.openMobile).toBe(false)
        expect(document.activeElement).toBe(trigger)
    })

    it('closes on a press outside, but not inside or on the trigger', () => {
        const provider = mountMobile()
        const press = element => element.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true }))

        provider.setOpenMobile(true)

        press(container().querySelector('a'))
        expect(provider.openMobile).toBe(true)

        press(document.querySelector('[data-sidebar="trigger"]'))
        expect(provider.openMobile).toBe(true)

        press(document.querySelector('p'))
        expect(provider.openMobile).toBe(false)
    })
})
