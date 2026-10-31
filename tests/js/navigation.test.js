import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerAnchor } from '../../resources/js/components/anchor.js'
import { register as registerBackToTop } from '../../resources/js/components/back-to-top.js'

beforeAll(() => {
    registerAnchor()
    registerBackToTop()
})

afterEach(() => {
    document.body.innerHTML = ''
    set(window, { scrollY: 0 })
})

const frame = () => new Promise(resolve => requestAnimationFrame(resolve))

// jsdom does no layout or scrolling: give an element the sizes a browser would.
const set = (element, sizes) => {
    for (const [name, value] of Object.entries(sizes)) Object.defineProperty(element, name, { configurable: true, value, writable: true })
}

describe('anchor', () => {
    // jsdom does no layout: where each section starts, against a 768px window (band: 153.6px).
    function place(tops) {
        for (const [id, top] of Object.entries(tops)) document.getElementById(id).getBoundingClientRect = () => ({ top })
    }

    function mount() {
        document.body.innerHTML = `
            <eso-anchor>
                <a data-slot="anchor-item" href="#intro">Intro</a>
                <a data-slot="anchor-item" href="#usage">Usage</a>
                <a data-slot="anchor-item" href="#api">API</a>
                <a data-slot="anchor-item" href="#missing">Missing</a>
            </eso-anchor>
            <section id="intro"></section><section id="usage"></section><section id="api"></section>`

        place({ intro: 100, usage: 500, api: 900 })
        document.querySelector('eso-anchor').connectedCallback()

        return document.querySelector('eso-anchor')
    }

    const current = element => [...element.querySelectorAll('[aria-current="location"]')].map(item => item.textContent)

    it('marks the last section that has reached the top fifth of the window', async () => {
        const element = mount()

        expect(current(element)).toEqual(['Intro'])

        place({ intro: -600, usage: 40, api: 400 })
        document.dispatchEvent(new Event('scroll'))
        await frame()

        expect(current(element)).toEqual(['Usage'])
    })

    it('marks the last section once its scroller is at the bottom', async () => {
        const element = mount()
        const scroller = document.createElement('div')
        scroller.append(...document.querySelectorAll('section'))
        document.body.append(scroller)
        set(scroller, { scrollTop: 400, clientHeight: 600, scrollHeight: 1000 })

        place({ intro: -600, usage: 40, api: 400 })
        scroller.dispatchEvent(new Event('scroll'))
        await frame()

        expect(current(element)).toEqual(['API'])
    })

    it('puts aria-current back when a morph drops it', async () => {
        const element = mount()

        element.querySelector('[aria-current]').removeAttribute('aria-current')
        await Promise.resolve()

        expect(current(element)).toEqual(['Intro'])
    })
})

describe('back to top', () => {
    it('shows past the threshold and scrolls the window back up', async () => {
        window.scrollTo = vi.fn()
        document.body.innerHTML = '<eso-back-to-top data-threshold="400" hidden><button>Up</button></eso-back-to-top>'
        const element = document.querySelector('eso-back-to-top')

        expect(element.hidden).toBe(true)

        set(window, { scrollY: 500 })
        window.dispatchEvent(new Event('scroll'))
        expect(element.hidden).toBe(false)

        element.querySelector('button').click()
        expect(window.scrollTo).toHaveBeenCalledWith({ top: 0, behavior: 'smooth' })

        set(window, { scrollY: 100 })
        window.dispatchEvent(new Event('scroll'))
        expect(element.hidden).toBe(true)
    })

    it('follows a scrolling container given as its target', () => {
        document.body.innerHTML = '<main id="main"></main><eso-back-to-top data-target="#main" data-threshold="200" hidden><button>Up</button></eso-back-to-top>'
        const main = document.getElementById('main')
        main.scrollTo = vi.fn()
        const element = document.querySelector('eso-back-to-top')

        set(main, { scrollTop: 300 })
        main.dispatchEvent(new Event('scroll'))
        expect(element.hidden).toBe(false)

        element.click()
        expect(main.scrollTo).toHaveBeenCalledWith({ top: 0, behavior: 'smooth' })
    })
})
