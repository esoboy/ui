import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerMentions } from '../../resources/js/components/mentions.js'

beforeAll(() => registerMentions())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(prefix = ['@']) {
    document.body.innerHTML = `
        <eso-mentions data-slot="mentions" data-prefix='${JSON.stringify(prefix)}' data-split=" ">
            <textarea data-slot="textarea" role="combobox" aria-expanded="false" aria-controls="mentions-1" data-mentions-input></textarea>
            <div id="mentions-1" popover="manual" role="listbox" data-slot="mentions-content">
                <div data-slot="mentions-list">
                    <div role="option" data-slot="mentions-item" data-value="zhangsan">张三 Zhang San</div>
                    <div role="option" data-slot="mentions-item" data-value="lisi">李四 Li Si</div>
                    <div role="option" data-slot="mentions-item" data-value="wangwu" aria-disabled="true">王五 Wang Wu</div>
                    <div role="option" data-slot="mentions-item" data-value="design" data-prefix="#">Design</div>
                </div>
                <div data-slot="mentions-empty">No results found.</div>
            </div>
            <div data-slot="mentions-mirror"><span></span><span data-mentions-caret></span></div>
        </eso-mentions>`

    const element = document.querySelector('eso-mentions')
    const seen = []

    element.input.addEventListener('mentions-search', event => seen.push(['search', event.detail]))
    element.input.addEventListener('mentions-select', event => seen.push(['select', event.detail]))
    // What the component itself announces to wire:model, not the typing below.
    element.input.addEventListener('input', () => ! typing && seen.push(['input', element.input.value]))

    return [element, seen]
}

let typing = false

const type = (element, value, caret = value.length) => {
    typing = true
    element.input.value = value
    element.input.setSelectionRange(caret, caret)
    element.input.dispatchEvent(new Event('input', { bubbles: true }))
    typing = false
}

const key = (element, name) => {
    const event = new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true })

    element.input.dispatchEvent(event)

    return event
}

const shown = element => element.items.filter(item => ! item.hidden).map(item => item.dataset.value)
const highlighted = element => element.querySelector('[data-highlighted="true"]')?.dataset.value

describe('suggesting', () => {
    it('opens after the prefix with the items of that prefix, the first highlighted', () => {
        const [element, seen] = mount()

        type(element, 'Thanks @')

        expect(element.isOpen).toBe(true)
        expect(element.input.getAttribute('aria-expanded')).toBe('true')
        expect(shown(element)).toEqual(['zhangsan', 'lisi', 'wangwu'])
        expect(highlighted(element)).toBe('zhangsan')
        expect(element.input.getAttribute('aria-activedescendant')).toBe(element.querySelector('[data-value="zhangsan"]').id)
        expect(seen).toEqual([['search', { prefix: '@', query: '' }]])
    })

    it('filters by what follows the prefix, on text or value', () => {
        const [element, seen] = mount()

        type(element, '@li')
        expect(shown(element)).toEqual(['lisi'])

        type(element, '@张')
        expect(shown(element)).toEqual(['zhangsan'])

        type(element, '@nobody')
        expect(element.content.hasAttribute('data-empty')).toBe(true)
        expect(seen.map(([, detail]) => detail.query)).toEqual(['li', '张', 'nobody'])
    })

    it('only takes a prefix at the start of a word, and closes after a space', () => {
        const [element] = mount()

        type(element, 'mail me at a@b')
        expect(element.isOpen).toBe(false)

        type(element, '@zhang ')
        expect(element.isOpen).toBe(false)
    })

    it('follows the caret back into a mention', () => {
        const [element] = mount()

        type(element, '@li and more', 3)

        expect(element.isOpen).toBe(true)
        expect(shown(element)).toEqual(['lisi'])
    })

    it('keeps items to their prefix when there are several', () => {
        const [element] = mount(['@', '#'])

        type(element, 'Tag #')

        expect(shown(element)).toEqual(['design'])
    })
})

describe('choosing', () => {
    it('moves past disabled items and inserts prefix, value and split on Enter', () => {
        const [element, seen] = mount()

        type(element, 'Thanks @ for it', 8)
        key(element, 'ArrowDown')
        expect(highlighted(element)).toBe('lisi')

        key(element, 'ArrowDown')
        expect(highlighted(element)).toBe('zhangsan')

        key(element, 'ArrowUp')
        const enter = key(element, 'Enter')

        expect(enter.defaultPrevented).toBe(true)
        expect(element.input.value).toBe('Thanks @lisi  for it')
        expect(element.input.selectionStart).toBe(13)
        expect(element.isOpen).toBe(false)
        expect(seen).toEqual([['search', { prefix: '@', query: '' }], ['input', 'Thanks @lisi  for it'], ['select', { prefix: '@', value: 'lisi' }]])
    })

    it('chooses with Tab and with the pointer, without leaving the textarea', () => {
        const [element] = mount()

        type(element, '@z')
        key(element, 'Tab')
        expect(element.input.value).toBe('@zhangsan ')

        type(element, '@zhangsan @l')
        const press = new MouseEvent('pointerdown', { bubbles: true, cancelable: true })

        element.querySelector('[data-value="lisi"]').dispatchEvent(press)

        expect(press.defaultPrevented).toBe(true)
        expect(element.input.value).toBe('@zhangsan @lisi ')
    })

    it('closes on Escape before a dialog around it would', () => {
        const [element] = mount()

        type(element, '@')

        expect(key(element, 'Escape').defaultPrevented).toBe(true)
        expect(element.isOpen).toBe(false)
    })

    it('leaves Enter to the textarea while closed', () => {
        const [element] = mount()

        type(element, 'line')

        expect(key(element, 'Enter').defaultPrevented).toBe(false)
    })
})

it('filters the items Livewire renders for a search', async () => {
    const [element] = mount()

    type(element, '@w')
    element.list.insertAdjacentHTML('beforeend', '<div role="option" data-slot="mentions-item" data-value="wendy">Wendy</div>')
    await Promise.resolve()

    expect(shown(element)).toEqual(['wangwu', 'wendy'])
    expect(highlighted(element)).toBe('wendy')
})
