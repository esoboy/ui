import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerCountdown } from '../../resources/js/components/countdown.js'
import { countdowns, format } from '../../resources/js/support/countdown.js'

beforeAll(() => registerCountdown())

afterEach(() => {
    vi.useRealTimers()
    countdowns.clear()
    document.body.innerHTML = ''
})

describe('format', () => {
    // The same cases as the server's Support\Countdown.
    it.each([
        [90061, 'HH:mm:ss', '25:01:01'],
        [90061, 'D [days] HH:mm:ss', '1 days 01:01:01'],
        [90061, 'D天H时m分s秒', '1天1时1分1秒'],
        [61, 'ss', '61'],
        [0, 'mm:ss', '00:00'],
    ])('formats %is as %s', (seconds, pattern, text) => {
        expect(format(seconds * 1000, pattern)).toBe(text)
    })

    it('rounds up to the second it is in', () => {
        expect(format(1500, 'ss')).toBe('02')
        expect(format(1000, 'ss')).toBe('01')
    })
})

describe('countdown', () => {
    function mount(attributes) {
        document.body.innerHTML = `<eso-countdown ${attributes}>server</eso-countdown>`

        return document.querySelector('eso-countdown')
    }

    it('counts down from the time left the server sent, then dispatches finish once', () => {
        vi.useFakeTimers()
        const element = mount('data-remaining="2000" data-format="mm:ss"')
        const finished = vi.fn()
        element.addEventListener('finish', finished)

        expect(element.textContent).toBe('00:02')

        vi.advanceTimersByTime(1000)
        expect(element.textContent).toBe('00:01')

        vi.advanceTimersByTime(1000)
        expect(element.textContent).toBe('00:00')
        expect(finished).toHaveBeenCalledTimes(1)

        vi.advanceTimersByTime(5000)
        expect(finished).toHaveBeenCalledTimes(1)
    })

    it('does not finish when it arrives already over', async () => {
        vi.useFakeTimers()
        const element = document.createElement('eso-countdown')
        element.dataset.remaining = '0'
        const finished = vi.fn()
        element.addEventListener('finish', finished)

        document.body.append(element)
        vi.advanceTimersByTime(2000)

        expect(element.textContent).toBe('00:00:00')
        expect(finished).not.toHaveBeenCalled()
    })

    it('keeps a named end when it is rendered again, and restarts with ui-countdown', () => {
        vi.useFakeTimers()
        mount('data-name="sale" data-seconds="10" data-remaining="10000" data-format="ss"')
        vi.advanceTimersByTime(3000)

        // A morph that replaced the element: the same name keeps counting from where it was.
        const again = mount('data-name="sale" data-seconds="10" data-remaining="10000" data-format="ss"')
        expect(again.textContent).toBe('07')

        window.dispatchEvent(new CustomEvent('ui-countdown', { detail: { name: 'sale', seconds: 30 } }))
        expect(again.textContent).toBe('30')

        window.dispatchEvent(new CustomEvent('ui-countdown', { detail: { name: 'sale', seconds: null } }))
        expect(again.textContent).toBe('10')
    })

    it('follows a new time left from the server only when it is a second or more off', async () => {
        vi.useFakeTimers()
        const element = mount('data-remaining="10000" data-format="ss"')

        vi.advanceTimersByTime(1500)
        element.dataset.remaining = '8800'
        await Promise.resolve()
        expect(element.textContent).toBe('09')

        element.dataset.remaining = '60000'
        await Promise.resolve()
        expect(element.textContent).toBe('60')
    })

    it('puts its time back when a morph writes the server\'s text', async () => {
        vi.useFakeTimers()
        const element = mount('data-remaining="5000" data-format="ss"')

        element.textContent = '99'
        await Promise.resolve()

        expect(element.textContent).toBe('05')
    })
})
