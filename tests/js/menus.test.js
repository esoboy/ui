import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerDrawer } from '../../resources/js/components/drawer.js'
import { register as registerDropdownMenu } from '../../resources/js/components/dropdown-menu.js'
import { register as registerHoverCard } from '../../resources/js/components/hover-card.js'
import { register as registerNavigationMenu } from '../../resources/js/components/navigation-menu.js'

beforeAll(() => {
    registerDropdownMenu()
    registerHoverCard()
    registerNavigationMenu()
    registerDrawer()
})

afterEach(() => {
    vi.useRealTimers()
    document.body.innerHTML = ''
})

const isOpen = element => element.matches(':popover-open')

describe('context menu', () => {
    it('opens at the pointer, 4px lower', () => {
        document.body.innerHTML = `
            <div data-slot="context-menu-trigger">Area</div>
            <div id="menu" popover="auto" role="menu" tabindex="-1" data-slot="context-menu-content"><button role="menuitem">Back</button></div>`

        const event = new MouseEvent('contextmenu', { bubbles: true, cancelable: true, clientX: 40, clientY: 50 })
        document.querySelector('[data-slot="context-menu-trigger"]').dispatchEvent(event)

        const menu = document.getElementById('menu')

        expect(event.defaultPrevented).toBe(true)
        expect(isOpen(menu)).toBe(true)
        expect([menu.style.left, menu.style.top]).toEqual(['40px', '54px'])
        expect(document.activeElement).toBe(menu)
    })
})

describe('menubar', () => {
    function mount() {
        document.body.innerHTML = `
            <div data-slot="menubar">
                <button data-slot="menubar-trigger" commandfor="file">File</button>
                <div id="file" popover="auto" role="menu" data-slot="menubar-content"><button role="menuitem">New</button></div>
                <button data-slot="menubar-trigger" commandfor="view">View</button>
                <div id="view" popover="auto" role="menu" data-slot="menubar-content"><button role="menuitem">Reload</button></div>
            </div>`
    }

    const menu = id => document.getElementById(id)

    it('switches menus when another trigger is hovered while one is open', () => {
        mount()
        menu('file').showPopover()

        document.querySelector('[commandfor="view"]').dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))

        expect(isOpen(menu('view'))).toBe(true)
    })

    it('does not open on hover when nothing is open', () => {
        mount()

        document.querySelector('[commandfor="view"]').dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))

        expect(isOpen(menu('view'))).toBe(false)
    })

    it('keeps the menu it switched to open when that trigger is then clicked', () => {
        mount()
        menu('file').showPopover()

        const trigger = document.querySelector('[commandfor="view"]')
        trigger.dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))

        const click = new MouseEvent('click', { bubbles: true, cancelable: true })
        trigger.dispatchEvent(click)

        expect(click.defaultPrevented).toBe(true)
        expect(isOpen(menu('view'))).toBe(true)
    })

    it('moves to the next menu with ArrowRight from inside a menu, wrapping', () => {
        mount()
        menu('view').showPopover()
        menu('view').querySelector('button').focus()

        document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true, cancelable: true }))

        expect(isOpen(menu('file'))).toBe(true)
    })
})

describe('hover card', () => {
    it('opens after its delay and closes after leaving, unless the pointer moves into the card', () => {
        vi.useFakeTimers()
        document.body.innerHTML = `
            <a data-hover-card-trigger data-delay="600" data-close-delay="300">@nextjs</a>
            <div id="card" popover="manual" data-slot="hover-card-content">Card</div>`

        const trigger = document.querySelector('a')
        const card = document.getElementById('card')

        trigger.dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))
        vi.advanceTimersByTime(599)
        expect(isOpen(card)).toBe(false)
        vi.advanceTimersByTime(1)
        expect(isOpen(card)).toBe(true)

        trigger.dispatchEvent(new MouseEvent('pointerout', { bubbles: true, relatedTarget: card }))
        card.dispatchEvent(new MouseEvent('pointerover', { bubbles: true, relatedTarget: trigger }))
        vi.advanceTimersByTime(400)
        expect(isOpen(card)).toBe(true)

        card.dispatchEvent(new MouseEvent('pointerout', { bubbles: true, relatedTarget: document.body }))
        vi.advanceTimersByTime(300)
        expect(isOpen(card)).toBe(false)
    })
})

describe('navigation menu', () => {
    function mount() {
        document.body.innerHTML = `
            <nav data-slot="navigation-menu">
                <button data-slot="navigation-menu-trigger" aria-expanded="false">One</button>
                <div id="one" popover="auto" data-slot="navigation-menu-content"><a href="#">Link</a></div>
            </nav>`

        return [document.querySelector('button'), document.getElementById('one')]
    }

    it('opens on hover and closes on leave', () => {
        vi.useFakeTimers()
        const [trigger, content] = mount()

        trigger.dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))
        vi.advanceTimersByTime(50)
        expect(isOpen(content)).toBe(true)
        expect(trigger.getAttribute('aria-expanded')).toBe('true')

        trigger.dispatchEvent(new MouseEvent('pointerout', { bubbles: true, relatedTarget: document.body }))
        vi.advanceTimersByTime(50)
        expect(isOpen(content)).toBe(false)
        expect(trigger.getAttribute('aria-expanded')).toBe('false')
    })

    it('keeps a menu opened by click when the pointer leaves', () => {
        vi.useFakeTimers()
        const [trigger, content] = mount()

        trigger.click()
        trigger.dispatchEvent(new MouseEvent('pointerout', { bubbles: true, relatedTarget: document.body }))
        vi.advanceTimersByTime(100)
        expect(isOpen(content)).toBe(true)

        trigger.click()
        expect(isOpen(content)).toBe(false)
    })

    it('keeps a hover-opened menu open when its trigger is clicked', () => {
        vi.useFakeTimers()
        const [trigger, content] = mount()

        trigger.dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))
        vi.advanceTimersByTime(50)
        trigger.click()

        expect(isOpen(content)).toBe(true)
        expect(content.dataset.openedBy).toBe('click')
    })
})

describe('drawer', () => {
    function mount() {
        document.body.innerHTML = `
            <dialog id="goal" data-slot="drawer-popup" data-swipe-direction="down" data-swipe-axis="y">
                <div data-slot="drawer-content"><p>Body</p><button>Submit</button></div>
            </dialog>`

        const drawer = document.getElementById('goal')

        drawer.showModal()
        Object.defineProperty(drawer, 'offsetHeight', { value: 400 })

        return drawer
    }

    const swipe = (element, from, to) => {
        element.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true, button: 0, clientY: from }))
        window.dispatchEvent(new MouseEvent('pointermove', { clientY: to }))
        window.dispatchEvent(new MouseEvent('pointerup', { clientY: to }))
    }

    it('closes when swiped down past a quarter', () => {
        const drawer = mount()

        swipe(drawer.querySelector('p'), 100, 250)

        expect(drawer.open).toBe(false)
    })

    it('springs back from a short swipe', () => {
        const drawer = mount()

        swipe(drawer.querySelector('p'), 100, 105)

        expect(drawer.open).toBe(true)
        expect(drawer.style.getPropertyValue('--drawer-swipe-movement-y')).toBe('')
    })

    it('ignores swipes starting on controls', () => {
        const drawer = mount()

        swipe(drawer.querySelector('button'), 100, 300)

        expect(drawer.open).toBe(true)
    })
})
