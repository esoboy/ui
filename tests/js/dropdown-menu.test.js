import { beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { register as registerDropdownMenu } from '../../resources/js/components/dropdown-menu.js'

let menu
let sub

beforeAll(() => registerDropdownMenu())

beforeEach(() => {
    document.body.innerHTML = `
        <div id="menu" popover="auto" role="menu" tabindex="-1" data-slot="dropdown-menu-content">
            <button role="menuitem" data-slot="dropdown-menu-item">Profile</button>
            <button role="menuitem" data-slot="dropdown-menu-item" disabled>Settings</button>
            <button role="menuitem" data-slot="dropdown-menu-sub-trigger" commandfor="sub">Invite</button>
            <div id="sub" popover="auto" role="menu" tabindex="-1" data-slot="dropdown-menu-sub-content">
                <button role="menuitem" data-slot="dropdown-menu-item">Email</button>
                <button role="menuitem" data-slot="dropdown-menu-item">Message</button>
            </div>
            <label data-slot="dropdown-menu-checkbox-item"><input type="checkbox" role="menuitemcheckbox">Status bar</label>
        </div>
    `
    menu = document.getElementById('menu')
    sub = document.getElementById('sub')
})

const item = text => [...document.querySelectorAll('[role^="menuitem"]')].find(element => (element.textContent || element.parentElement.textContent).trim() === text)
const press = key => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true }))
const focused = () => (document.activeElement.textContent || document.activeElement.parentElement.textContent).trim()

describe('opening', () => {
    it('focuses the menu when it opens', () => {
        menu.showPopover()

        expect(document.activeElement).toBe(menu)
    })

    it('leaves focus alone when a submenu opens', () => {
        menu.showPopover()
        item('Invite').focus()
        sub.showPopover()

        expect(focused()).toBe('Invite')
    })
})

describe('keyboard', () => {
    beforeEach(() => menu.showPopover())

    it('moves through enabled items of the same menu, wrapping around', () => {
        press('ArrowDown')
        expect(focused()).toBe('Profile')

        press('ArrowDown')
        expect(focused()).toBe('Invite')

        press('ArrowDown')
        expect(focused()).toBe('Status bar')

        press('ArrowDown')
        expect(focused()).toBe('Profile')

        press('ArrowUp')
        expect(focused()).toBe('Status bar')
    })

    it('jumps to the first and last items', () => {
        press('End')
        expect(focused()).toBe('Status bar')

        press('Home')
        expect(focused()).toBe('Profile')
    })

    it('opens a submenu with ArrowRight and leaves it with ArrowLeft', () => {
        item('Invite').focus()

        press('ArrowRight')
        expect(sub.matches(':popover-open')).toBe(true)
        expect(focused()).toBe('Email')

        press('ArrowDown')
        expect(focused()).toBe('Message')

        press('ArrowLeft')
        expect(sub.matches(':popover-open')).toBe(false)
        expect(focused()).toBe('Invite')
    })
})

describe('pointer', () => {
    const hover = text => {
        const target = item(text)
        ;(target.closest('label') ?? target).dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))
    }

    it('highlights the hovered item, labels included', () => {
        hover('Profile')
        expect(focused()).toBe('Profile')

        hover('Status bar')
        expect(focused()).toBe('Status bar')
    })

    it('opens a submenu on hover and closes it when moving to a sibling', () => {
        hover('Invite')
        expect(sub.matches(':popover-open')).toBe(true)

        hover('Email')
        expect(sub.matches(':popover-open')).toBe(true)

        hover('Profile')
        expect(sub.matches(':popover-open')).toBe(false)
    })
})
