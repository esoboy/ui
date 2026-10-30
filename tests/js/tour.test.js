import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerTour } from '../../resources/js/components/tour.js'

beforeAll(() => registerTour())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount() {
    document.body.innerHTML = `
        <button id="start">Start</button>
        <input id="search">
        <button id="profile" style="anchor-name: --menu">Profile</button>
        <eso-tour id="onboarding" data-slot="tour">
            <template data-tour-step data-target="#search" data-side="bottom" data-align="center"><h2 data-slot="tour-title">Search</h2><div data-slot="tour-description">Find anything.</div></template>
            <template data-tour-step data-target="#profile" data-side="left" data-align="start"><div data-slot="tour-description">Your account.</div></template>
            <template data-tour-step data-target="#missing" data-side="bottom" data-align="center"><h2 data-slot="tour-title">All set</h2><div data-slot="tour-description">That is all.</div></template>
            <div popover="manual" data-slot="tour-spotlight" aria-hidden="true"></div>
            <div popover="manual" role="dialog" aria-modal="false" data-slot="tour-content">
                <button data-tour-action="close">Close</button>
                <div data-slot="tour-body"></div>
                <div data-slot="tour-footer">
                    <span data-slot="tour-counter" data-format=":current of :total"></span>
                    <button data-tour-action="previous">Previous</button>
                    <button data-tour-action="next">Next</button>
                    <button data-tour-action="finish">Finish</button>
                </div>
            </div>
        </eso-tour>`

    const tour = document.querySelector('eso-tour')
    const seen = []

    tour.addEventListener('tour-change', event => seen.push(['change', event.detail]))
    tour.addEventListener('tour-close', event => seen.push(['close', event.detail]))

    return [tour, seen]
}

const part = (tour, slot) => tour.querySelector(`[data-slot="${slot}"]`)
const action = (tour, name) => tour.querySelector(`[data-tour-action="${name}"]`)
const click = (tour, name) => action(tour, name).click()
const command = (tour, name) => tour.dispatchEvent(Object.assign(new Event('command'), { command: name }))
const anchors = element => element.style.getPropertyValue('anchor-name')
const visible = tour => ['previous', 'next', 'finish'].filter(name => ! action(tour, name).hidden)

describe('starting', () => {
    it('opens on the first step, anchored to its target, from a command', () => {
        const [tour, seen] = mount()

        document.getElementById('start').focus()
        command(tour, '--start')

        expect(part(tour, 'tour-content').matches(':popover-open')).toBe(true)
        expect(part(tour, 'tour-spotlight').matches(':popover-open')).toBe(true)
        expect(tour.step).toBe(0)
        expect(anchors(document.getElementById('search'))).toBe('--tour-target')
        expect(document.getElementById('search').hasAttribute('data-tour-target')).toBe(true)
        expect(part(tour, 'tour-body').textContent).toBe('SearchFind anything.')
        expect(part(tour, 'tour-counter').textContent).toBe('1 of 3')
        expect(part(tour, 'tour-content').dataset).toMatchObject({ side: 'bottom', align: 'center' })
        expect(visible(tour)).toEqual(['next'])
        expect(document.activeElement).toBe(action(tour, 'next'))
        expect(seen).toEqual([['change', { step: 0 }]])
    })

    it('names the panel after the step', () => {
        const [tour] = mount()

        tour.start()

        expect(part(tour, 'tour-content').getAttribute('aria-labelledby')).toBe('onboarding-title')
        expect(part(tour, 'tour-content').getAttribute('aria-describedby')).toBe('onboarding-description')
        expect(document.getElementById('onboarding-title').textContent).toBe('Search')

        click(tour, 'next')

        expect(part(tour, 'tour-content').hasAttribute('aria-labelledby')).toBe(false)
        expect(document.getElementById('onboarding-description').textContent).toBe('Your account.')
    })

    it('starts from the server at the step given', () => {
        const [tour, seen] = mount()

        window.dispatchEvent(new CustomEvent('ui-tour', { detail: { name: 'onboarding', open: true, step: 1 } }))

        expect(tour.step).toBe(1)
        expect(seen).toEqual([['change', { step: 1 }]])
    })

    it('does nothing without steps', () => {
        const [tour] = mount()

        tour.querySelectorAll('template').forEach(step => step.remove())
        tour.start()

        expect(tour.isOpen).toBe(false)
    })
})

describe('stepping', () => {
    it('moves between the steps, keeping the target\'s own anchor names', () => {
        const [tour, seen] = mount()
        const search = document.getElementById('search')
        const profile = document.getElementById('profile')

        tour.start()
        click(tour, 'next')

        expect(anchors(search)).toBe('')
        expect(search.hasAttribute('data-tour-target')).toBe(false)
        expect(anchors(profile)).toBe('--menu, --tour-target')
        expect(part(tour, 'tour-content').dataset).toMatchObject({ side: 'left', align: 'start' })
        expect(part(tour, 'tour-counter').textContent).toBe('2 of 3')
        expect(visible(tour)).toEqual(['previous', 'next'])

        click(tour, 'previous')

        expect(anchors(profile)).toBe('--menu')
        expect(seen.map(([, detail]) => detail.step)).toEqual([0, 1, 0])
    })

    it('puts the panel in the middle when the target is missing', () => {
        const [tour] = mount()

        tour.start(2)

        expect(part(tour, 'tour-content').hasAttribute('data-side')).toBe(false)
        expect(part(tour, 'tour-content').hasAttribute('data-align')).toBe(false)
        expect(part(tour, 'tour-spotlight').hasAttribute('data-empty')).toBe(true)
        expect(visible(tour)).toEqual(['previous', 'finish'])
        expect(document.activeElement).toBe(action(tour, 'finish'))
    })

    it('ignores steps out of range', () => {
        const [tour, seen] = mount()

        tour.start()
        tour.go(5)
        tour.go(-1)

        expect(tour.step).toBe(0)
        expect(seen).toHaveLength(1)
    })
})

describe('closing', () => {
    it('finishes on the last step and gives the focus back', () => {
        const [tour, seen] = mount()

        document.getElementById('start').focus()
        tour.start(2)
        click(tour, 'finish')

        expect(tour.isOpen).toBe(false)
        expect(part(tour, 'tour-spotlight').matches(':popover-open')).toBe(false)
        expect(tour.step).toBe(-1)
        expect(document.activeElement).toBe(document.getElementById('start'))
        expect(seen.at(-1)).toEqual(['close', { step: 2, finished: true }])
    })

    it('closes unfinished by its button, Escape, a command or the server', () => {
        const ways = [
            tour => click(tour, 'close'),
            () => document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true })),
            tour => command(tour, '--close'),
            () => window.dispatchEvent(new CustomEvent('ui-tour', { detail: { name: 'onboarding', open: false, step: null } })),
        ]

        for (const close of ways) {
            const [tour, seen] = mount()
            const profile = document.getElementById('profile')

            tour.start(1)
            close(tour)

            expect(tour.isOpen).toBe(false)
            expect(anchors(profile)).toBe('--menu')
            expect(seen.at(-1)).toEqual(['close', { step: 1, finished: false }])
        }
    })

    it('leaves Escape to a menu or dialog the target opened, then closes', () => {
        for (const markup of ['<div id="layer" popover>Menu</div>', '<div id="layer" popover="auto">Menu</div>', '<dialog id="layer">Dialog</dialog>']) {
            const [tour] = mount()

            document.body.insertAdjacentHTML('beforeend', markup)

            const layer = document.getElementById('layer')
            const escape = () => {
                const event = new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true })

                document.dispatchEvent(event)

                return event
            }

            tour.start(1)
            layer instanceof HTMLDialogElement ? layer.showModal() : layer.showPopover()

            // Not canceled: the browser goes on to close the menu or dialog.
            expect(escape().defaultPrevented).toBe(false)
            expect(tour.isOpen).toBe(true)

            layer instanceof HTMLDialogElement ? layer.close() : layer.hidePopover()

            expect(escape().defaultPrevented).toBe(true)
            expect(tour.isOpen).toBe(false)
        }
    })

    it('leaves Escape to a component that took it', () => {
        const [tour] = mount()
        const search = document.getElementById('search')

        search.addEventListener('keydown', event => event.preventDefault())
        tour.start()
        search.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }))

        expect(tour.isOpen).toBe(true)
    })

    it('still closes on Escape while a manual popover such as the toaster is shown', () => {
        const [tour] = mount()

        document.body.insertAdjacentHTML('beforeend', '<div id="toaster" popover="manual"></div>')
        document.getElementById('toaster').showPopover()
        tour.start()
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))

        expect(tour.isOpen).toBe(false)
    })

    it('reports nothing when it was not open', () => {
        const [tour, seen] = mount()

        tour.close()

        expect(seen).toEqual([])
    })
})

describe('after a Livewire update', () => {
    it('anchors the step\'s target again', () => {
        const [tour] = mount()
        const search = document.getElementById('search')

        tour.start()
        search.removeAttribute('style')
        tour.refresh()

        expect(anchors(search)).toBe('--tour-target')
    })
})
