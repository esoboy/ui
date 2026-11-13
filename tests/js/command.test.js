import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import commandProvider from '../../resources/js/alpine/command.js'
import { engineDriver, cleanupControllers } from './alpine-harness.js'


afterEach(() => {
    cleanupControllers()
    document.body.innerHTML = ''
})

function mount(attributes = '') {
    document.body.innerHTML = `
        <eso-command ${attributes}>
            <input data-command-input>
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

    const root = engineDriver(commandProvider, document.querySelector('eso-command'))
    window.Alpine.bind(root, { 'x-on:keydown': event => root.key(event), 'x-on:pointermove': event => root.hover(event) })
    return root
}

const selected = command => command.querySelector('[data-selected="true"]')?.textContent
const key = (command, name) => command.input.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

it('highlights a visible server result after a refresh', () => {
    const command = mount()
    expect(selected(command)).toBe('Calendar')
    command.items[0].hidden = true
    command.refresh()
    expect(selected(command)).toBe('Search Emoji')
    expect(command.input.getAttribute('aria-activedescendant')).toBe(command.items[2].id)
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

describe('aria', () => {
    it('points the input at the list it controls, again after a morph strips it', async () => {
        const command = mount()

        const list = command.querySelector('[data-slot="command-list"]')
        expect(list.id).toBeTruthy()
        expect(command.input.getAttribute('aria-controls')).toBe(list.id)

        command.input.removeAttribute('aria-controls')
        commandProvider().init.call({ $el: command, $id: name => name, get input() { return command.input }, refresh: () => command.refresh() })
        await Promise.resolve()

        expect(command.input.getAttribute('aria-controls')).toBe(list.id)
    })
})
