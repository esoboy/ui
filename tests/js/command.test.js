import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { matches, register as registerCommand } from '../../resources/js/components/command.js'

beforeAll(() => registerCommand())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(attributes = '') {
    document.body.innerHTML = `
        <eso-command ${attributes}>
            <input data-slot="command-input">
            <div data-slot="command-list">
                <div data-slot="command-empty">No results</div>
                <div data-slot="command-group">
                    <div data-slot="command-item">Calendar</div>
                    <div data-slot="command-item" aria-disabled="true">Calculator</div>
                    <div data-slot="command-item" data-keywords="smiley">Search Emoji</div>
                </div>
                <div data-slot="command-separator"></div>
                <div data-slot="command-group">
                    <div data-slot="command-item">Profile</div>
                </div>
            </div>
        </eso-command>`

    return document.querySelector('eso-command')
}

const type = (command, text) => {
    command.input.value = text
    command.input.dispatchEvent(new Event('input', { bubbles: true }))
}
const visible = command => command.items.filter(item => ! item.hidden).map(item => item.textContent)
const selected = command => command.querySelector('[data-selected="true"]')?.textContent
const key = (command, name) => command.input.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

describe('matching', () => {
    it('matches letters in order', () => {
        expect(matches('cln', 'calendar')).toBe(true)
        expect(matches('pfl', 'profile')).toBe(true)
        expect(matches('nlc', 'calendar')).toBe(false)
        expect(matches('se em', 'search emoji')).toBe(true)
    })
})

describe('filtering', () => {
    it('highlights the first item to start with', () => {
        const command = mount()

        expect(selected(command)).toBe('Calendar')
        expect(command.input.getAttribute('aria-activedescendant')).toBe(command.items[0].id)
    })

    it('hides what does not match, empty groups and separators', () => {
        const command = mount()

        type(command, 'pro')

        expect(visible(command)).toEqual(['Profile'])
        expect(command.querySelector('[data-slot="command-group"]').hidden).toBe(true)
        expect(command.querySelector('[data-slot="command-separator"]').hidden).toBe(true)
        expect(selected(command)).toBe('Profile')
    })

    it('matches keywords', () => {
        const command = mount()

        type(command, 'smiley')

        expect(visible(command)).toEqual(['Search Emoji'])
    })

    it('shows the empty state when nothing matches', () => {
        const command = mount()

        type(command, 'zzz')

        expect(command.hasAttribute('data-empty')).toBe(true)
    })

    it('can leave filtering to the server', () => {
        const command = mount('data-should-filter="false"')

        type(command, 'zzz')

        expect(visible(command)).toHaveLength(4)
    })
})

describe('keyboard', () => {
    it('moves over disabled items and stops at the ends', () => {
        const command = mount()

        key(command, 'ArrowDown')
        expect(selected(command)).toBe('Search Emoji')

        key(command, 'End')
        key(command, 'ArrowDown')
        expect(selected(command)).toBe('Profile')

        key(command, 'Home')
        key(command, 'ArrowUp')
        expect(selected(command)).toBe('Calendar')
    })

    it('clicks the highlighted item on Enter', () => {
        const command = mount()
        const click = vi.fn()

        command.items[2].addEventListener('click', click)
        key(command, 'ArrowDown')
        key(command, 'Enter')

        expect(click).toHaveBeenCalledOnce()
    })

    it('highlights on hover', () => {
        const command = mount()

        command.items[3].dispatchEvent(new MouseEvent('pointermove', { bubbles: true }))

        expect(selected(command)).toBe('Profile')
    })
})
