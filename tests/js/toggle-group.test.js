import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerRadioGroup } from '../../resources/js/components/radio-group.js'

beforeAll(() => registerRadioGroup())

afterEach(() => {
    document.body.innerHTML = ''
})

const inputEvents = group => {
    const seen = []
    group.addEventListener('input', event => event.target === group && seen.push(JSON.stringify(group.value)))
    return seen
}

it('deselects the pressed item of a single toggle group when clicked again', () => {
    document.body.innerHTML = `
        <eso-radio-group data-deselectable>
            <label><input type="radio" value="left" checked></label>
            <label><input type="radio" value="right"></label>
        </eso-radio-group>`
    const group = document.querySelector('eso-radio-group')
    const seen = inputEvents(group)
    const [left, right] = group.querySelectorAll('input')

    left.click()
    expect(group.value).toBeNull()
    expect(left.checked).toBe(false)

    right.click()
    expect(group.value).toBe('right')

    expect(seen).toEqual(['null', '"right"'])
})

it('keeps a plain radio group from deselecting', () => {
    document.body.innerHTML = '<eso-radio-group><input type="radio" value="a" checked></eso-radio-group>'
    const group = document.querySelector('eso-radio-group')

    group.querySelector('input').click()

    expect(group.value).toBe('a')
})

it('exposes a checkbox group as an array', () => {
    document.body.innerHTML = `
        <eso-checkbox-group name="styles">
            <input type="checkbox" value="b" checked>
            <input type="checkbox" value="i">
            <input type="checkbox" value="u">
        </eso-checkbox-group>`
    const group = document.querySelector('eso-checkbox-group')
    const seen = inputEvents(group)
    const [, italic] = group.querySelectorAll('input')

    expect(group.value).toEqual(['b'])

    italic.click()
    expect(group.value).toEqual(['b', 'i'])
    expect(seen).toEqual(['["b","i"]'])

    group.value = ['u']
    expect([...group.querySelectorAll('input')].map(input => input.checked)).toEqual([false, false, true])

    expect([...group.querySelectorAll('input')].map(input => input.name)).toEqual(['styles', 'styles', 'styles'])
})
