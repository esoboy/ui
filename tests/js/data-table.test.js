import { afterAll, afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import dataTable from '../../resources/js/alpine/data-table.js'
import { engineDriver, cleanupControllers, provider } from './alpine-harness.js'

beforeAll(() => {
    // jsdom does not implement layout or checkVisibility.
    Element.prototype.checkVisibility = function () {
        return getComputedStyle(this.closest('tr')).display !== 'none'
    }
})

afterAll(() => {
    delete Element.prototype.checkVisibility
})

afterEach(() => {
    cleanupControllers()
    document.body.innerHTML = ''
})

function mount() {
    const wire = { teams: [], selectRows: vi.fn() }
    const data = provider(dataTable, `
        <eso-data-table data-selection="teams">
            <table>
                <thead><tr><th><input type="checkbox" data-data-table-select-all></th></tr></thead>
                <tbody>
                    <tr><td><input type="checkbox" value="1" data-data-table-select-row></td></tr>
                    <tr><td><input type="checkbox" value="2" data-data-table-select-row></td></tr>
                    <tr><td><input type="checkbox" value="3" data-data-table-select-row disabled></td></tr>
                    <tr hidden><td><input type="checkbox" value="4" data-data-table-select-row></td></tr>
                    <tr style="display: none"><td><input type="checkbox" value="5" data-data-table-select-row></td></tr>
                </tbody>
            </table>
        </eso-data-table>`, { wire })

    for (const box of rows()) box.addEventListener('change', () => {
        wire.teams = box.checked ? [...wire.teams, box.value] : wire.teams.filter(value => value !== box.value)
    })

    return { data, wire }
}

const all = () => document.querySelector('[data-data-table-select-all]')
const rows = () => [...document.querySelectorAll('[data-data-table-select-row]')]

it('sends one server action with enabled visible row ids', () => {
    const { data, wire } = mount()
    data._selectAll(true)
    expect(wire.selectRows).toHaveBeenCalledWith('teams', ['1', '2'], true)
    data._selectAll(false)
    expect(wire.selectRows).toHaveBeenLastCalledWith('teams', ['1', '2'], false)
})

it('shows mixed while only some rows are checked', () => {
    mount()

    rows()[0].click()

    expect(all().indeterminate).toBe(true)
    expect(all().checked).toBe(false)

    rows()[1].click()

    expect(all().indeterminate).toBe(false)
    expect(all().checked).toBe(true)
})

it('follows rows checked by a morph', async () => {
    const { wire } = mount()

    wire.teams = [1]
    rows()[0].setAttribute('checked', '')

    await vi.waitFor(() => expect(all().indeterminate).toBe(true))
})

describe('expanding', () => {
    function tree() {
        document.body.innerHTML = `
            <eso-data-table>
                <table><tbody>
                    <tr data-row-id="eng" data-expanded><td><button data-data-table-row-toggle="tree" aria-expanded="false"></button></td></tr>
                    <tr data-row-id="eng/platform" data-parent="eng" hidden><td><button data-data-table-row-toggle="tree" aria-expanded="false"></button></td></tr>
                    <tr data-row-id="eng/platform/api" data-parent="eng/platform" hidden><td></td></tr>
                    <tr data-row-id="ops"><td><button data-data-table-row-toggle="detail" aria-expanded="false"></button></td></tr>
                    <tr data-slot="data-table-row-detail" data-detail-for="ops" hidden><td>Ops details</td></tr>
                </tbody></table>
            </eso-data-table>`

        return engineDriver(dataTable, document.querySelector('eso-data-table'))
    }

    const visible = () => [...document.querySelectorAll('tr[data-row-id]')].filter(row => ! row.hidden).map(row => row.dataset.rowId)
    const toggle = id => document.querySelector(`tr[data-row-id="${id}"] [data-data-table-row-toggle]`)

    it('opens rows expanded by default, and leaves terminal rows without a tree toggle', () => {
        tree()

        expect(visible()).toEqual(['eng', 'eng/platform', 'ops'])
        expect(toggle('eng').getAttribute('aria-expanded')).toBe('true')
        expect(toggle('eng/platform/api')).toBeNull()
        expect(toggle('ops').dataset.dataTableRowToggle).toBe('detail')
    })

    it('shows a row only while all its ancestors are expanded, remembering inner ones', () => {
        tree()

        toggle('eng/platform').click()
        expect(visible()).toEqual(['eng', 'eng/platform', 'eng/platform/api', 'ops'])

        toggle('eng').click()
        expect(visible()).toEqual(['eng', 'ops'])

        toggle('eng').click()
        expect(visible()).toEqual(['eng', 'eng/platform', 'eng/platform/api', 'ops'])
    })

    it('opens a row detail, and keeps it open when a morph hides it again', async () => {
        tree()
        const detail = document.querySelector('[data-detail-for="ops"]')

        toggle('ops').click()
        expect(detail.hidden).toBe(false)
        expect(toggle('ops').getAttribute('aria-expanded')).toBe('true')

        detail.setAttribute('hidden', 'hidden')
        await Promise.resolve()

        expect(detail.hidden).toBe(false)
    })

    it('applies a changed server expansion value to an already rendered row', async () => {
        tree()
        const row = document.querySelector('tr[data-row-id="eng"]')

        row.setAttribute('data-expanded', 'false')
        await vi.waitFor(() => expect(visible()).toEqual(['eng', 'ops']))

        row.setAttribute('data-expanded', 'true')
        await vi.waitFor(() => expect(visible()).toEqual(['eng', 'eng/platform', 'ops']))
    })
})

describe('sticky columns', () => {
    // jsdom does no layout: every cell is as wide as its data-width.
    function sticky() {
        document.body.innerHTML = `
            <eso-data-table>
                <div data-slot="table-container">
                    <table><tbody><tr>
                        <td data-sticky="start" data-width="32"></td>
                        <td data-sticky="start" data-width="180"></td>
                        <td data-width="400"></td>
                        <td data-sticky="end" data-width="48"></td>
                        <td data-sticky="end" data-width="60"></td>
                    </tr></tbody></table>
                </div>
            </eso-data-table>`

        for (const cell of document.querySelectorAll('td')) cell.getBoundingClientRect = () => ({ width: Number(cell.dataset.width) })
        return engineDriver(dataTable, document.querySelector('eso-data-table'))
    }

    it('offsets each sticky cell by the sticky cells before it on its edge', () => {
        sticky()

        expect([...document.querySelectorAll('td')].map(cell => cell.style.getPropertyValue('--sticky-offset'))).toEqual(['0px', '32px', '', '60px', '0px'])
    })

    it('says which edges have more to scroll to', () => {
        sticky()
        const container = document.querySelector('[data-slot="table-container"]')

        for (const [name, value] of Object.entries({ scrollWidth: 720, clientWidth: 500, scrollLeft: 0 })) Object.defineProperty(container, name, { configurable: true, writable: true, value })
        container.dispatchEvent(new Event('scroll'))
        expect([container.hasAttribute('data-scroll-start'), container.hasAttribute('data-scroll-end')]).toEqual([false, true])

        container.scrollLeft = 220
        container.dispatchEvent(new Event('scroll'))
        expect([container.hasAttribute('data-scroll-start'), container.hasAttribute('data-scroll-end')]).toEqual([true, false])
    })
})

it('lights a merged cell with whichever of its rows is hovered', () => {
    document.body.innerHTML = `
        <eso-data-table>
            <table><tbody>
                <tr><td rowspan="3">Hardware</td><td>Laptop</td></tr>
                <tr><td>Monitor</td></tr>
                <tr><td>Keyboard</td></tr>
                <tr><td>Software</td><td>License</td></tr>
            </tbody></table>
        </eso-data-table>`

    engineDriver(dataTable, document.querySelector('eso-data-table'))
    const [, , keyboard, license] = document.querySelectorAll('tbody tr')
    const lit = () => [...document.querySelectorAll('[data-span-hover]')].map(cell => cell.textContent)

    keyboard.dispatchEvent(new Event('pointerover', { bubbles: true }))
    expect(lit()).toEqual(['Hardware'])

    license.dispatchEvent(new Event('pointerover', { bubbles: true }))
    expect(lit()).toEqual([])
})
