import { afterEach, describe, expect, it, vi } from 'vitest'
import { register as registerDialog } from '../../resources/js/components/dialog.js'
import { register as registerPopover } from '../../resources/js/components/popover.js'

afterEach(() => {
    document.body.innerHTML = ''
})

describe('a bound dialog', () => {
    it('takes a value Livewire set before the element was defined', () => {
        document.body.innerHTML = '<eso-dialog wire:model="editing"><dialog data-slot="dialog-content"></dialog></eso-dialog>'

        const element = document.querySelector('eso-dialog')

        element.value = true
        registerDialog()

        expect(element.querySelector('dialog').open).toBe(true)
        expect(element.value).toBe(true)
    })

    it('opens and closes with its value, and says when it closes another way', () => {
        registerDialog()
        document.body.innerHTML = '<eso-dialog wire:model="editing"><dialog data-slot="dialog-content"></dialog></eso-dialog>'

        const element = document.querySelector('eso-dialog')
        const dialog = element.querySelector('dialog')
        const seen = []

        element.addEventListener('change', event => event.target === element && seen.push(element.value))

        element.value = true
        expect(dialog.open).toBe(true)

        element.value = false
        expect(dialog.open).toBe(false)
        expect(seen).toEqual([])

        element.value = true
        dialog.close()

        expect(seen).toEqual([false])
    })
})

describe('a bound popover', () => {
    it('opens against its trigger, and says when it opens or closes', () => {
        registerPopover()
        document.body.innerHTML = `
            <button data-popover-trigger command="toggle-popover" commandfor="">Open</button>
            <eso-popover wire:model="shown"><div data-slot="popover-content" popover="auto"></div></eso-popover>`

        const element = document.querySelector('eso-popover')
        const content = element.querySelector('[popover]')
        const shown = vi.spyOn(content, 'showPopover')
        const seen = []

        element.addEventListener('change', event => event.target === element && seen.push(element.value))

        element.value = true

        expect(shown).toHaveBeenCalledWith({ source: document.querySelector('button') })
        expect(seen).toEqual([])

        content.hidePopover()
        document.querySelector('button').click()

        expect(seen).toEqual([false, true])
    })
})
