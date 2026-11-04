import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerMessageScroller } from '../../resources/js/components/message-scroller.js'

/*
 * jsdom does no layout, so rows get their height from data-h and stack in the content;
 * the viewport is VIEWPORT px tall at the top of the page and scrolls instantly.
 */
const VIEWPORT = 300
const nativeRect = Element.prototype.getBoundingClientRect

beforeAll(() => {
    registerMessageScroller()

    Element.prototype.getBoundingClientRect = function () {
        const viewport = this.closest('[data-slot="message-scroller-viewport"]')

        if (this === viewport) return box(0, VIEWPORT)
        if (! viewport || this.dataset.h === undefined) return nativeRect.call(this)

        let top = 0

        for (const row of this.parentElement.children) {
            if (row === this) break
            top += height(row)
        }

        return box(top - viewport.scrollTop, height(this))
    }
})

afterAll(() => {
    Element.prototype.getBoundingClientRect = nativeRect
})

afterEach(() => {
    document.body.innerHTML = ''
})

const box = (top, h) => ({ top, bottom: top + h, height: h, left: 0, right: 100, width: 100, x: 0, y: top })
const height = row => (row.hidden ? 0 : Number(row.dataset.h ?? parseFloat(row.style.height || '0')))
const frame = () => new Promise(resolve => setTimeout(resolve, 30))

function row(id, h, anchor = false) {
    return `<div data-slot="message-scroller-item" data-message-id="${id}" data-scroll-anchor="${anchor}" data-h="${h}"></div>`
}

function mount(rows, attributes = '') {
    const host = document.createElement('div')

    host.innerHTML = `
        <eso-message-scroller data-pending-scroll ${attributes}>
            <div data-slot="message-scroller-viewport" data-pending-scroll>
                <div data-slot="message-scroller-content">${rows.join('')}<div data-message-scroller-spacer hidden></div></div>
            </div>
            <button data-message-scroller-button="end" data-active="false"></button>
            <button data-message-scroller-button="start" data-active="false"></button>
        </eso-message-scroller>`

    const viewport = host.querySelector('[data-slot="message-scroller-viewport"]')
    const content = host.querySelector('[data-slot="message-scroller-content"]')
    let top = 0

    const total = () => [...content.children].reduce((sum, child) => sum + height(child), 0)

    Object.defineProperties(viewport, {
        clientHeight: { value: VIEWPORT },
        scrollHeight: { get: () => Math.max(VIEWPORT, total()) },
        scrollTop: { get: () => top, set: value => (top = Math.max(0, Math.min(value, Math.max(VIEWPORT, total()) - VIEWPORT))) },
    })
    viewport.scrollTo = ({ top: value }) => (viewport.scrollTop = value)

    document.body.append(host)

    return host.querySelector('eso-message-scroller')
}

const offset = (scroller, id) => scroller.items.find(item => item.dataset.messageId === id).getBoundingClientRect().top
const button = (scroller, direction) => scroller.querySelector(`[data-message-scroller-button="${direction}"]`)

describe('opening', () => {
    it('opens at the end and shows the viewport', async () => {
        const scroller = mount([row('a', 200), row('b', 200), row('c', 200)])

        expect(scroller.viewport.scrollTop).toBe(300)
        expect(scroller.hasAttribute('data-pending-scroll')).toBe(false)
        expect(scroller.viewport.hasAttribute('data-pending-scroll')).toBe(false)

        await frame()
        expect(scroller.getAttribute('data-scrollable')).toBe('start')
        expect(button(scroller, 'start').dataset.active).toBe('true')
        expect(button(scroller, 'end').inert).toBe(true)
    })

    it('opens at the last anchored turn, with a peek of the row before', () => {
        const scroller = mount([row('a', 400), row('b', 100, true), row('c', 500)], 'data-default-scroll-position="last-anchor" data-scroll-previous-item-peek="64"')

        expect(offset(scroller, 'b')).toBe(64)
    })

    it('opens at the end when the last turn fits', () => {
        const scroller = mount([row('a', 400), row('b', 100, true), row('c', 100)], 'data-default-scroll-position="last-anchor"')

        expect(scroller.viewport.scrollTop).toBe(300)
    })

    it('opens at the start when told to', () => {
        const scroller = mount([row('a', 400), row('b', 400)], 'data-default-scroll-position="start"')

        expect(scroller.viewport.scrollTop).toBe(0)
    })
})

describe('turns', () => {
    it('settles a new anchored turn near the top, making room below it', () => {
        const scroller = mount([row('a', 400), row('b', 100)])

        scroller.content.insertAdjacentHTML('beforeend', row('c', 50, true))

        return frame().then(() => {
            expect(offset(scroller, 'c')).toBe(64)
            expect(scroller.spacer.hidden).toBe(false)
            expect(parseFloat(scroller.spacer.style.height)).toBe(186)
        })
    })

    it('keeps the reader in place when history is prepended', async () => {
        const scroller = mount([row('marker', 20), row('a', 200), row('b', 200), row('c', 200)], 'data-default-scroll-position="start"')

        scroller.viewport.scrollTop = 250
        scroller.viewport.dispatchEvent(new Event('scroll'))
        const before = offset(scroller, 'b')

        // Older rows arrive below a fixed "Load earlier" row.
        scroller.items[0].insertAdjacentHTML('afterend', row('old1', 150) + row('old2', 150))
        await frame()

        expect(offset(scroller, 'b')).toBe(before)
    })
})

describe('following', () => {
    it('follows appended rows at the live edge with auto-scroll', async () => {
        const scroller = mount([row('a', 400)], 'data-auto-scroll')

        scroller.content.insertAdjacentHTML('beforeend', row('b', 200))
        await frame()

        expect(scroller.viewport.scrollTop).toBe(300)
        expect(button(scroller, 'end').dataset.active).toBe('false')
    })

    it('lets go once the reader scrolls', async () => {
        const scroller = mount([row('a', 400)], 'data-auto-scroll')

        scroller.viewport.dispatchEvent(new WheelEvent('wheel'))
        scroller.viewport.scrollTop = 0
        scroller.viewport.dispatchEvent(new Event('scroll'))
        scroller.content.insertAdjacentHTML('beforeend', row('b', 200))
        await frame()

        expect(scroller.viewport.scrollTop).toBe(0)
        expect(button(scroller, 'end').dataset.active).toBe('true')
    })
})

describe('commands', () => {
    it('jumps to a message', () => {
        const scroller = mount([row('a', 400), row('b', 100), row('c', 400)], 'data-default-scroll-position="start"')

        expect(scroller.scrollToMessage('b')).toBe(true)
        expect(offset(scroller, 'b')).toBe(0)
        expect(scroller.scrollToMessage('missing')).toBe(false)
    })

    it('waits for a message that is not rendered yet', () => {
        const scroller = mount([])

        expect(scroller.scrollToMessage('b')).toBe(true)

        scroller.content.insertAdjacentHTML('afterbegin', row('a', 400) + row('b', 100) + row('c', 400))

        return frame().then(() => expect(offset(scroller, 'b')).toBe(0))
    })

    it('scrolls to the start or end from its buttons when active', async () => {
        const scroller = mount([row('a', 400), row('b', 400)])

        await frame()
        button(scroller, 'start').click()
        expect(scroller.viewport.scrollTop).toBe(0)

        await frame()
        button(scroller, 'end').click()
        expect(scroller.viewport.scrollTop).toBe(500)
    })
})
