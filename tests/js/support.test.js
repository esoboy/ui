import { afterEach, describe, expect, it } from 'vitest'
import { announce, connected, define, disconnected, readValue, writeHidden } from '../../resources/js/support/element.js'

afterEach(() => {
    document.body.innerHTML = ''
})

describe('define', () => {
    it('defines once and accepts the same element again', () => {
        class Probe extends HTMLElement {}

        define('eso-probe', Probe)
        define('eso-probe', Probe)

        expect(customElements.get('eso-probe')).toBe(Probe)
    })

    it('refuses a name another script already took', () => {
        customElements.define('eso-taken', class extends HTMLElement {})

        expect(() => define('eso-taken', class extends HTMLElement {})).toThrow('<eso-taken> is already defined by another script')
    })
})

describe('connected', () => {
    it('keeps only the listeners of the last connection', () => {
        const element = document.createElement('div')
        let clicks = 0

        element.addEventListener('click', () => clicks++, { signal: connected(element) })
        element.addEventListener('click', () => clicks++, { signal: connected(element) })
        element.click()

        expect(clicks).toBe(1)
    })

    it('removes them when the element disconnects', () => {
        const element = document.createElement('div')
        let clicks = 0

        element.addEventListener('click', () => clicks++, { signal: connected(element) })
        disconnected(element)
        element.click()

        expect(clicks).toBe(0)
    })
})

describe('announce', () => {
    it('dispatches input then change from the element', () => {
        const element = document.createElement('div')
        const seen = []

        document.body.append(element)
        document.addEventListener('input', event => seen.push(`input:${event.target === element}`))
        document.addEventListener('change', event => seen.push(`change:${event.target === element}`))

        announce(element)

        expect(seen).toEqual(['input:true', 'change:true'])
    })
})

describe('readValue', () => {
    it('parses data-value, falling back when it is missing or broken', () => {
        const element = document.createElement('div')

        expect(readValue(element, [])).toEqual([])

        element.dataset.value = '["a","b"]'
        expect(readValue(element)).toEqual(['a', 'b'])

        element.dataset.value = '{broken'
        expect(readValue(element, 'fallback')).toBe('fallback')
    })
})

describe('writeHidden', () => {
    const inputs = element => [...element.querySelectorAll(':scope > input[type="hidden"]')].map(input => `${input.name}=${input.value}`)

    it('writes one input for a value and name[] inputs for a list', () => {
        const element = document.createElement('div')

        writeHidden(element, 'color', '#fff')
        expect(inputs(element)).toEqual(['color=#fff'])

        writeHidden(element, 'color', null)
        expect(inputs(element)).toEqual(['color='])

        writeHidden(element, 'tags', ['a', 'b'])
        expect(inputs(element)).toEqual(['tags[]=a', 'tags[]=b'])

        writeHidden(element, 'tags', [])
        expect(inputs(element)).toEqual([])
    })

    it('leaves matching inputs in place and does nothing without a name', () => {
        const element = document.createElement('div')

        writeHidden(element, 'tags', ['a'])
        const input = element.querySelector('input')

        writeHidden(element, 'tags', ['a'])
        expect(element.querySelector('input')).toBe(input)

        writeHidden(element, undefined, ['b'])
        expect(inputs(element)).toEqual(['tags[]=a'])
    })
})
