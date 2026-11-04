import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerRadioGroup } from '../../resources/js/components/radio-group.js'

beforeAll(() => registerRadioGroup())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(html) {
    document.body.innerHTML = html

    return document.querySelector('eso-radio-group')
}

const items = `
    <input type="radio" value="starter">
    <input type="radio" value="pro">
`

describe('value', () => {
    it('reports the checked radio', () => {
        const group = mount(`<eso-radio-group><input type="radio" value="starter"><input type="radio" value="pro" checked></eso-radio-group>`)

        expect(group.value).toBe('pro')
    })

    it('is null when nothing is checked', () => {
        expect(mount(`<eso-radio-group>${items}</eso-radio-group>`).value).toBeNull()
    })

    it('checks the matching radio when set', () => {
        const group = mount(`<eso-radio-group>${items}</eso-radio-group>`)

        group.value = 'pro'
        expect(group.querySelector('[value="pro"]').checked).toBe(true)

        group.value = null
        expect(group.value).toBeNull()
    })

    it('compares numbers as strings', () => {
        const group = mount(`<eso-radio-group><input type="radio" value="1"><input type="radio" value="2"></eso-radio-group>`)

        group.value = 2

        expect(group.value).toBe('2')
    })
})

describe('events', () => {
    it('re-dispatches radio input and change events from the group', () => {
        const group = mount(`<eso-radio-group>${items}</eso-radio-group>`)
        const seen = []

        for (const type of ['input', 'change']) {
            group.addEventListener(type, event => event.target === group && seen.push([type, group.value]))
        }

        group.querySelector('[value="pro"]').click()

        expect(seen).toEqual([['input', 'pro'], ['change', 'pro']])
    })

    it('ignores radios of a nested group', () => {
        const outer = mount(`<eso-radio-group id="outer"><eso-radio-group id="inner"><input type="radio" value="x"></eso-radio-group></eso-radio-group>`)
        const fromOuter = []

        outer.addEventListener('input', event => event.target === outer && fromOuter.push(event))
        outer.querySelector('[value="x"]').click()

        expect(outer.inputs).toHaveLength(0)
        expect(fromOuter).toHaveLength(0)
    })
})

describe('names', () => {
    it('gives unnamed radios a shared name', () => {
        const group = mount(`<eso-radio-group>${items}</eso-radio-group>`)
        const names = group.inputs.map(radio => radio.name)

        expect(names[0]).toMatch(/^ui-radio-group-\d+$/)
        expect(new Set(names).size).toBe(1)
    })

    it('uses the group name for classic form posts', () => {
        const group = mount(`<eso-radio-group name="plan">${items}</eso-radio-group>`)

        expect(group.inputs.map(radio => radio.name)).toEqual(['plan', 'plan'])
    })

    it('keeps names the radios already have', () => {
        const group = mount(`<eso-radio-group name="plan"><input type="radio" name="custom" value="a"></eso-radio-group>`)

        expect(group.inputs[0].name).toBe('custom')
    })

    it('names radios added or stripped by a later morph', async () => {
        const group = mount(`<eso-radio-group name="plan">${items}</eso-radio-group>`)

        group.insertAdjacentHTML('beforeend', '<input type="radio" value="team">')
        group.inputs[0].removeAttribute('name')

        await vi.waitFor(() => expect(group.inputs.map(radio => radio.name)).toEqual(['plan', 'plan', 'plan']))
    })
})
