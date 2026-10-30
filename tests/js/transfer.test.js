import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerTransfer } from '../../resources/js/components/transfer.js'

beforeAll(() => registerTransfer())

afterEach(() => {
    document.body.innerHTML = ''
})

const options = [['a', 'Alpha'], ['b', 'Beta'], ['c', 'Gamma'], ['d', 'Delta']]

function mount(value = ['b'], { disabled = [] } = {}) {
    const item = ([key, label], index) => `<label data-slot="transfer-item" data-value="${key}" data-index="${index}"><input type="checkbox" ${disabled.includes(key) ? 'disabled' : ''}><span>${label}</span></label>`
    const list = name => `
        <div data-slot="transfer-list" data-list="${name}">
            <input type="checkbox" data-transfer-all>
            <span data-slot="transfer-count"></span>
            <input data-transfer-search>
            <div data-slot="transfer-items">${options.map((option, index) => [option, index]).filter(([[key]]) => value.includes(key) === (name === 'target')).map(([option, index]) => item(option, index)).join('')}</div>
        </div>`

    document.body.innerHTML = `
        <eso-transfer data-value='${JSON.stringify(value)}' data-name="roles">
            ${list('source')}
            <button data-transfer-move="target" disabled></button>
            <button data-transfer-move="source" disabled></button>
            ${list('target')}
        </eso-transfer>`

    const element = document.querySelector('eso-transfer')
    const seen = []

    element.addEventListener('change', event => event.target === element && seen.push(element.value))

    return [element, seen]
}

const list = (element, name) => element.querySelector(`[data-list="${name}"]`)
const labels = (element, name) => [...list(element, name).querySelectorAll('[data-slot="transfer-item"]:not([hidden])')].map(item => item.textContent)
const check = (element, label) => [...element.querySelectorAll('[data-slot="transfer-item"]')].find(item => item.textContent === label).querySelector('input').click()
const move = (element, to) => element.querySelector(`[data-transfer-move="${to}"]`).click()
const count = (element, name) => list(element, name).querySelector('[data-slot="transfer-count"]').textContent

describe('transfer', () => {
    it('counts and enables moves as items are checked', () => {
        const [element] = mount()

        expect(count(element, 'source')).toBe('0/3')
        expect(element.querySelector('[data-transfer-move="target"]').disabled).toBe(true)

        check(element, 'Gamma')

        expect(count(element, 'source')).toBe('1/3')
        expect(element.querySelector('[data-transfer-move="target"]').disabled).toBe(false)
        expect(list(element, 'source').querySelector('[data-transfer-all]').indeterminate).toBe(true)
    })

    it('moves checked items over, keeping the options order, and announces from itself', () => {
        const [element, seen] = mount()

        check(element, 'Delta')
        check(element, 'Alpha')
        move(element, 'target')

        expect(labels(element, 'target')).toEqual(['Alpha', 'Beta', 'Delta'])
        expect(labels(element, 'source')).toEqual(['Gamma'])
        expect(seen).toEqual([['a', 'b', 'd']])
        expect(count(element, 'target')).toBe('0/3')
        expect([...element.querySelectorAll(':scope > input[type="hidden"]')].map(input => input.value)).toEqual(['a', 'b', 'd'])

        check(element, 'Beta')
        move(element, 'source')

        expect(seen.at(-1)).toEqual(['a', 'd'])
        expect(labels(element, 'source')).toEqual(['Beta', 'Gamma'])
    })

    it('checks all shown, enabled items from the header, within the search', () => {
        const [element] = mount([], { disabled: ['c'] })
        const search = list(element, 'source').querySelector('[data-transfer-search]')

        search.value = 'ta'
        search.dispatchEvent(new Event('input', { bubbles: true }))
        expect(labels(element, 'source')).toEqual(['Beta', 'Delta'])

        list(element, 'source').querySelector('[data-transfer-all]').click()
        expect(count(element, 'source')).toBe('2/4')

        search.value = ''
        search.dispatchEvent(new Event('input', { bubbles: true }))
        list(element, 'source').querySelector('[data-transfer-all]').click()

        expect(count(element, 'source')).toBe('3/4')
        expect(list(element, 'source').querySelector('[data-transfer-all]').checked).toBe(true)
    })

    it('marks a list with nothing to show as empty', () => {
        const [element] = mount(['a', 'b', 'c', 'd'])

        expect(list(element, 'source').hasAttribute('data-empty')).toBe(true)
        expect(list(element, 'target').hasAttribute('data-empty')).toBe(false)
    })

    it('places items for values set by Livewire, without announcing', () => {
        const [element, seen] = mount()

        element.value = ['c']

        expect(labels(element, 'target')).toEqual(['Gamma'])
        expect(labels(element, 'source')).toEqual(['Alpha', 'Beta', 'Delta'])
        expect(seen).toEqual([])
    })
})
