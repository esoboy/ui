import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerPopover } from '../../resources/js/components/popover.js'
import { contentOf, triggerOf } from '../../resources/js/support/overlay.js'

beforeAll(() => registerPopover())

afterEach(() => {
    document.body.innerHTML = ''
})

const trigger = name => document.querySelector(`[data-name="${name}"]`)
const content = name => document.querySelector(`[data-slot="popover-content"][data-name="${name}"]`)

describe('pairing', () => {
    it('pairs each trigger with the content after it, nested ones inside out', () => {
        document.body.innerHTML = `
            <button data-popover-trigger data-name="a">A</button>
            <div data-slot="popover-content" data-name="a">
                <button data-popover-trigger data-name="b">B</button>
                <div data-slot="popover-content" data-name="b">b</div>
            </div>
            <span><button data-popover-trigger data-name="c">C</button></span>
            <button data-popover-trigger data-name="d">D</button>
            <div data-slot="popover-content" data-name="d">d</div>
            <div data-slot="popover-content" data-name="c">c</div>`

        for (const name of ['a', 'b', 'c', 'd']) {
            expect(contentOf(document.querySelector(`button[data-name="${name}"]`), 'popover')).toBe(content(name))
            expect(triggerOf(content(name), 'popover')).toBe(document.querySelector(`button[data-name="${name}"]`))
        }
    })

    it('keeps kinds apart, so one element can trigger two', () => {
        document.body.innerHTML = `
            <button data-tooltip-trigger data-dropdown-menu-trigger>More</button>
            <div data-slot="tooltip-content">More actions</div>
            <div data-slot="dropdown-menu-content" role="menu"></div>`

        const button = document.querySelector('button')

        expect(contentOf(button, 'tooltip')).toBe(document.querySelector('[data-slot="tooltip-content"]'))
        expect(contentOf(button, 'dropdown-menu')).toBe(document.querySelector('[data-slot="dropdown-menu-content"]'))
    })

    it('still pairs after an overlay comes before', () => {
        document.body.innerHTML = `
            <button data-popover-trigger data-name="b">B</button>
            <div data-slot="popover-content" data-name="b">b</div>`

        document.body.insertAdjacentHTML('afterbegin', '<button data-popover-trigger data-name="a">A</button><div data-slot="popover-content" data-name="a">a</div>')

        expect(contentOf(trigger('b'), 'popover')).toBe(content('b'))
    })
})

describe('invoker triggers', () => {
    it('point at their popover when pressed, and mirror it in aria-expanded', () => {
        document.body.innerHTML = `
            <button data-popover-trigger data-name="a" command="toggle-popover" commandfor="">A</button>
            <div data-slot="popover-content" data-name="a" popover="auto">a</div>`

        trigger('a').dispatchEvent(new MouseEvent('pointerdown', { bubbles: true }))

        expect(trigger('a').commandForElement).toBe(content('a'))

        trigger('a').click()

        expect(content('a').matches(':popover-open')).toBe(true)
        expect(trigger('a').getAttribute('aria-expanded')).toBe('true')

        trigger('a').click()

        expect(trigger('a').getAttribute('aria-expanded')).toBe('false')
    })

    it('open when a script clicks them', () => {
        document.body.innerHTML = `
            <button data-dropdown-menu-trigger data-name="m" command="toggle-popover" commandfor="">Menu</button>
            <div data-slot="dropdown-menu-content" popover="auto" role="menu"></div>`

        trigger('m').click()

        expect(document.querySelector('[role="menu"]').matches(':popover-open')).toBe(true)
    })
})
