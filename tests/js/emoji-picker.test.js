import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { register as registerEmojiPicker } from '../../resources/js/components/emoji-picker.js'

/*
 * A device that draws Emoji up to 14 and no country flags: an emoji it lacks comes out in
 * the fill colour (a box), a sequence it lacks as several glyphs.
 */
const DRAWN = ['😀', '🫠', '🙂', '😺', '🏁', '🐱']

beforeAll(() => {
    registerEmojiPicker()

    HTMLCanvasElement.prototype.getContext = function () {
        let text = ''

        return {
            clearRect() {},
            fillText(value) { text = value },
            measureText: value => ({ width: DRAWN.includes(value) || [...value].length === 1 ? 20 : 40 }),
            getImageData() {
                return { data: DRAWN.includes(text) ? [1, 2, 3, 255] : this.fillStyle === '#f00' ? [255, 0, 0, 255] : [0, 0, 255, 255] }
            },
        }
    }
})

const DATA = {
    'smileys-emotion': [
        ['😀', 'Grinning face', 'face|grin', 1],
        ['🙂', 'Slightly smiling face', 'face|smile', 1],
        ['😺', 'Grinning cat', 'cat|face', 0.6],
        ['🫠', 'Melting face', 'melt', 14],
        ['🫩', 'Face with bags under eyes', 'tired', 16],
    ],
    'animals-nature': [
        ['🐱', 'Cat face', 'cat|pet', 0.6],
    ],
    flags: [
        ['🏁', 'Chequered flag', 'race', 0.6],
        ['🇨🇳', 'Flag: China', 'CN', 2],
    ],
}

let source = 0

beforeEach(() => {
    globalThis.fetch = vi.fn(async () => ({ ok: true, json: async () => structuredClone(DATA) }))
})

afterEach(() => {
    document.body.innerHTML = ''
})

const CATEGORIES = ['smileys-emotion', 'animals-nature', 'flags']

function markup(src, columns = 2) {
    return `
        <eso-emoji-picker data-slot="emoji-picker" data-src="${src}" data-columns="${columns}">
            <div data-slot="emoji-picker-search-wrapper"><input type="text" data-slot="emoji-picker-search"></div>
            <div data-slot="emoji-picker-viewport">
                <div data-slot="emoji-picker-loading"></div>
                <div data-slot="emoji-picker-empty" hidden>No emoji found.</div>
                <div data-slot="emoji-picker-list" role="listbox" hidden>
                    ${CATEGORIES.map(category => `<div data-slot="emoji-picker-category" data-category="${category}"><div data-slot="emoji-picker-category-header">${category}</div><div data-slot="emoji-picker-emojis"></div></div>`).join('')}
                </div>
                <template data-template="emoji"><button type="button" role="option" aria-selected="false" tabindex="-1" data-slot="emoji-picker-emoji"></button></template>
            </div>
            <div data-slot="emoji-picker-footer">
                <div data-slot="emoji-picker-active-emoji" hidden></div>
                <span data-slot="emoji-picker-active-label" hidden></span>
                <span data-slot="emoji-picker-placeholder">Select an emoji…</span>
            </div>
        </eso-emoji-picker>`
}

// Each test fetches its own URL, as the data is kept per URL for the page.
async function mount({ columns = 2, wrap = html => html } = {}) {
    document.body.innerHTML = wrap(markup(`/emoji-${++source}.json`, columns))
    await vi.waitFor(() => expect(document.querySelector('[data-slot="emoji-picker-list"]').hidden).toBe(false))

    return document.querySelector('eso-emoji-picker')
}

const emojis = (picker, category) => [...picker.querySelectorAll(`${category ? `[data-category="${category}"] ` : ''}[data-slot="emoji-picker-emoji"]:not([hidden])`)].map(button => button.textContent)
const shownCategories = picker => [...picker.querySelectorAll('[data-slot="emoji-picker-category"]:not([hidden])')].map(category => category.dataset.category)

const search = (picker, value) => {
    picker.search.value = value
    picker.search.dispatchEvent(new Event('input', { bubbles: true }))
}

const key = (target, name) => {
    const event = new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true })

    target.dispatchEvent(event)

    return event
}

const footer = picker => [...picker.querySelector('[data-slot="emoji-picker-footer"]').children].filter(part => ! part.hidden).map(part => part.textContent).join(' ')

describe('loading', () => {
    it('fills the categories, leaving out what the device cannot draw', async () => {
        const picker = await mount()

        expect(emojis(picker, 'smileys-emotion')).toEqual(['😀', '🙂', '😺', '🫠'])
        expect(emojis(picker, 'flags')).toEqual(['🏁'])
        expect(picker.querySelector('[data-slot="emoji-picker-emoji"]').getAttribute('aria-label')).toBe('Grinning face')
        expect(picker.querySelector('[data-slot="emoji-picker-loading"]').hidden).toBe(true)
        expect(picker.querySelector('[data-slot="emoji-picker-empty"]').hidden).toBe(true)
        expect(picker.search.getAttribute('aria-controls')).toBe(picker.list.id)
        expect(picker.querySelector('[data-category="smileys-emotion"]').style.getPropertyValue('--emoji-picker-rows')).toBe('2')
    })

    it('fetches a data file once for every picker on the page', async () => {
        document.body.innerHTML = markup('/emoji-shared.json') + markup('/emoji-shared.json')

        await vi.waitFor(() => expect(document.querySelectorAll('[data-slot="emoji-picker-list"]:not([hidden])')).toHaveLength(2))

        expect(fetch).toHaveBeenCalledTimes(1)
    })

    it('says nothing was found when the data cannot load', async () => {
        fetch.mockImplementation(async () => ({ ok: false, status: 404 }))
        document.body.innerHTML = markup('/emoji-missing.json')

        const picker = document.querySelector('eso-emoji-picker')

        await vi.waitFor(() => expect(picker.querySelector('[data-slot="emoji-picker-empty"]').hidden).toBe(false))

        expect(picker.querySelector('[data-slot="emoji-picker-loading"]').hidden).toBe(true)
    })
})

describe('searching', () => {
    it('keeps the label and keyword matches, label matches first, and hides empty categories', async () => {
        const picker = await mount()

        search(picker, 'cat')

        expect(emojis(picker)).toEqual(['😺', '🐱'])
        expect(shownCategories(picker)).toEqual(['smileys-emotion', 'animals-nature'])
        expect(picker.active.textContent).toBe('😺')

        search(picker, 'grin')

        // 😺 is labelled "Grinning cat"; 😀 matches its label and a keyword.
        expect(emojis(picker, 'smileys-emotion')).toEqual(['😀', '😺'])
    })

    it('says so when nothing matches, and shows everything again in order when cleared', async () => {
        const picker = await mount()

        search(picker, 'zzz')

        expect(shownCategories(picker)).toEqual([])
        expect(picker.querySelector('[data-slot="emoji-picker-empty"]').hidden).toBe(false)

        search(picker, 'grin')
        search(picker, '')

        expect(emojis(picker, 'smileys-emotion')).toEqual(['😀', '🙂', '😺', '🫠'])
        expect(picker.querySelector('[data-slot="emoji-picker-empty"]').hidden).toBe(true)
        expect(picker.active).toBe(null)
    })
})

describe('the active emoji', () => {
    it('moves with the arrow keys, row by row across the categories', async () => {
        const picker = await mount()
        const active = () => picker.active?.textContent

        expect(key(picker.search, 'ArrowDown').defaultPrevented).toBe(true)
        expect(active()).toBe('😀')

        key(picker.search, 'ArrowRight')
        expect(active()).toBe('🙂')

        // Rows of two: 😀🙂 / 😺🫠, then 🐱, then 🏁.
        key(picker.search, 'ArrowDown')
        expect(active()).toBe('🫠')

        key(picker.search, 'ArrowDown')
        expect(active()).toBe('🐱')

        key(picker.viewport, 'ArrowRight')
        expect(active()).toBe('🏁')

        key(picker.search, 'ArrowRight')
        expect(active()).toBe('🏁')

        key(picker.search, 'ArrowUp')
        expect(active()).toBe('🐱')
    })

    it('leaves left and right to the search caret until an emoji is active', async () => {
        const picker = await mount()

        expect(key(picker.search, 'ArrowLeft').defaultPrevented).toBe(false)
        expect(picker.active).toBe(null)
    })

    it('follows the pointer and is named in the footer and to assistive technology', async () => {
        const picker = await mount()
        const melting = picker.querySelectorAll('[data-slot="emoji-picker-emoji"]')[3]

        expect(footer(picker)).toBe('Select an emoji…')

        melting.dispatchEvent(new Event('pointermove', { bubbles: true }))

        expect(picker.active).toBe(melting)
        expect(melting.hasAttribute('data-active')).toBe(true)
        expect(melting.getAttribute('aria-selected')).toBe('true')
        expect(picker.search.getAttribute('aria-activedescendant')).toBe(melting.id)
        expect(footer(picker)).toBe('🫠 Melting face')

        picker.dispatchEvent(new Event('pointerleave'))

        expect(picker.active).toBe(null)
        expect(melting.getAttribute('aria-selected')).toBe('false')
        expect(picker.search.hasAttribute('aria-activedescendant')).toBe(false)
        expect(footer(picker)).toBe('Select an emoji…')
    })
})

describe('picking', () => {
    const inPopover = html => `<button id="trigger">React</button><div id="popover" popover>${html}</div>`

    it('reports the emoji on a click or Enter, then closes the popover around the picker', async () => {
        const picker = await mount({ wrap: inPopover })
        const popover = document.getElementById('popover')
        const picked = []

        picker.addEventListener('emoji-select', event => picked.push(event.detail))
        popover.showPopover()
        picker.querySelectorAll('[data-slot="emoji-picker-emoji"]')[2].click()

        expect(picked).toEqual([{ emoji: '😺', label: 'Grinning cat' }])
        expect(popover.matches(':popover-open')).toBe(false)

        popover.showPopover()
        search(picker, 'melt')

        expect(key(picker.search, 'Enter').defaultPrevented).toBe(true)
        expect(picked.at(-1)).toEqual({ emoji: '🫠', label: 'Melting face' })
    })

    it('keeps the popover open when the event is canceled', async () => {
        const picker = await mount({ wrap: inPopover })
        const popover = document.getElementById('popover')

        picker.addEventListener('emoji-select', event => event.preventDefault())
        popover.showPopover()
        picker.querySelector('[data-slot="emoji-picker-emoji"]').click()

        expect(popover.matches(':popover-open')).toBe(true)
    })

    it('focuses the search as its popover opens and clears it as it closes', async () => {
        const picker = await mount({ wrap: inPopover })
        const popover = document.getElementById('popover')

        popover.showPopover()

        expect(document.activeElement).toBe(picker.search)

        search(picker, 'cat')
        popover.hidePopover()

        expect(picker.search.value).toBe('')
        expect(emojis(picker, 'smileys-emotion')).toHaveLength(4)
    })

    it('does nothing on Enter without an active emoji', async () => {
        const picker = await mount()
        const picked = vi.fn()

        picker.addEventListener('emoji-select', picked)

        expect(key(picker.search, 'Enter').defaultPrevented).toBe(false)
        expect(picked).not.toHaveBeenCalled()
    })
})
