import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerDialog } from '../../resources/js/components/dialog.js'

beforeAll(() => registerDialog())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(slot) {
    document.body.innerHTML = `<dialog id="d" data-slot="${slot}"><p>Inside</p></dialog>`
    const dialog = document.getElementById('d')
    dialog.getBoundingClientRect = () => ({ left: 100, right: 300, top: 100, bottom: 300 })
    dialog.showModal()
    return dialog
}

const clickAt = (element, x, y) => element.dispatchEvent(new MouseEvent('click', { bubbles: true, clientX: x, clientY: y }))

describe('backdrop', () => {
    it('closes a dialog or sheet when the click lands outside its box', () => {
        for (const slot of ['dialog-content', 'sheet-content']) {
            const dialog = mount(slot)

            clickAt(dialog, 10, 10)

            expect(dialog.open).toBe(false)
        }
    })

    it('keeps it open for clicks inside', () => {
        const dialog = mount('dialog-content')

        clickAt(dialog, 150, 150)
        clickAt(dialog.querySelector('p'), 10, 10)

        expect(dialog.open).toBe(true)
    })

    it('never closes an alert dialog', () => {
        const dialog = mount('alert-dialog-content')

        clickAt(dialog, 10, 10)

        expect(dialog.open).toBe(true)
    })
})

describe('Livewire', () => {
    it('opens and closes a dialog by name', () => {
        document.body.innerHTML = '<dialog id="edit" data-slot="dialog-content"></dialog>'
        const dialog = document.getElementById('edit')

        window.dispatchEvent(new CustomEvent('ui-dialog', { detail: { name: 'edit', open: true } }))
        expect(dialog.open).toBe(true)

        window.dispatchEvent(new CustomEvent('ui-dialog', { detail: { name: 'edit', open: false } }))
        expect(dialog.open).toBe(false)
    })

    it('ignores unknown names', () => {
        expect(() => window.dispatchEvent(new CustomEvent('ui-dialog', { detail: { name: 'missing', open: true } }))).not.toThrow()
    })
})
