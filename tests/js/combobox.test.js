import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerCombobox } from '../../resources/js/components/combobox.js'

beforeAll(() => registerCombobox())

afterEach(() => {
    document.body.innerHTML = ''
})

const items = `
    <div data-slot="combobox-item" data-value="next">Next.js<span data-slot="combobox-item-indicator"></span></div>
    <div data-slot="combobox-item" data-value="svelte">SvelteKit<span data-slot="combobox-item-indicator"></span></div>
    <div data-slot="combobox-item" data-value="remix" aria-disabled="true">Remix<span data-slot="combobox-item-indicator"></span></div>
    <div data-slot="combobox-item" data-value="astro">Astro<span data-slot="combobox-item-indicator"></span></div>`

function single(attributes = '') {
    document.body.innerHTML = `
        <eso-combobox ${attributes}>
            <div data-slot="input-group">
                <input data-combobox-input>
                <button data-combobox-trigger></button>
                <button data-combobox-clear></button>
            </div>
            <div data-slot="combobox-content" popover="manual">
                <div data-slot="combobox-empty">None</div>
                <div data-slot="combobox-list">${items}</div>
            </div>
        </eso-combobox>`

    return document.querySelector('eso-combobox')
}

function multiple(attributes = '') {
    document.body.innerHTML = `
        <eso-combobox data-multiple ${attributes}>
            <div data-slot="combobox-chips">
                <template data-combobox-chip><div data-slot="combobox-chip"><span data-combobox-chip-label></span><button data-combobox-chip-remove></button></div></template>
                <input data-combobox-input>
            </div>
            <div data-slot="combobox-content" popover="manual"><div data-slot="combobox-list">${items}</div></div>
        </eso-combobox>`

    return document.querySelector('eso-combobox')
}

const type = (combobox, text) => {
    combobox.input.value = text
    combobox.input.dispatchEvent(new Event('input', { bubbles: true }))
}
const key = (combobox, name) => combobox.input.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))
const visible = combobox => combobox.items.filter(item => ! item.hidden).map(item => item.dataset.value)
const highlighted = combobox => combobox.querySelector('[data-highlighted="true"]')?.dataset.value
const announced = combobox => {
    const seen = []
    combobox.addEventListener('input', event => event.target === combobox && seen.push(JSON.stringify(combobox.value)))
    return seen
}

describe('single', () => {
    it('shows the chosen item in the input', () => {
        const combobox = single('data-value=\'"svelte"\'')

        expect(combobox.value).toBe('svelte')
        expect(combobox.input.value).toBe('SvelteKit')
    })

    it('filters as you type, without highlighting, and shows the empty state', () => {
        const combobox = single()

        type(combobox, 'as')
        expect(combobox.isOpen).toBe(true)
        expect(visible(combobox)).toEqual(['astro'])
        expect(highlighted(combobox)).toBeUndefined()

        type(combobox, 'zzz')
        expect(combobox.content.hasAttribute('data-empty')).toBe(true)
    })

    it('chooses with the arrows and Enter, then closes', () => {
        const combobox = single()
        const seen = announced(combobox)

        combobox.input.click()
        key(combobox, 'ArrowDown')
        key(combobox, 'ArrowDown')
        expect(highlighted(combobox)).toBe('svelte')

        key(combobox, 'ArrowDown')
        expect(highlighted(combobox)).toBe('astro')

        key(combobox, 'Enter')
        expect(combobox.value).toBe('astro')
        expect(combobox.input.value).toBe('Astro')
        expect(combobox.isOpen).toBe(false)
        expect(seen).toEqual(['"astro"'])
    })

    it('restores the chosen text after an unfinished search', () => {
        const combobox = single('data-value=\'"next"\'')

        type(combobox, 'sv')
        key(combobox, 'Escape')

        expect(combobox.input.value).toBe('Next.js')
    })

    it('clears', () => {
        const combobox = single('data-value=\'"next"\'')
        const clear = combobox.querySelector('[data-combobox-clear]')

        expect(clear.hidden).toBe(false)
        clear.click()

        expect(combobox.value).toBeNull()
        expect(combobox.input.value).toBe('')
        expect(clear.hidden).toBe(true)
    })

    it('toggles from the trigger and closes on an outside press', () => {
        const combobox = single()

        combobox.querySelector('[data-combobox-trigger]').click()
        expect(combobox.isOpen).toBe(true)

        document.body.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true }))
        expect(combobox.isOpen).toBe(false)
    })

    it('takes values set by Livewire', () => {
        const combobox = single()

        combobox.value = 'next'

        expect(combobox.input.value).toBe('Next.js')
        expect(combobox.querySelector('[data-value="next"]').getAttribute('aria-selected')).toBe('true')
    })
})

describe('multiple', () => {
    it('renders chips for the chosen items', () => {
        const combobox = multiple('data-value=\'["next","astro"]\'')

        expect(combobox.value).toEqual(['next', 'astro'])
        expect([...combobox.querySelectorAll('[data-slot="combobox-chip"]')].map(chip => chip.textContent)).toEqual(['Next.js', 'Astro'])
    })

    it('toggles items and stays open', () => {
        const combobox = multiple()
        const seen = announced(combobox)

        combobox.input.click()
        combobox.querySelector('[data-slot="combobox-item"][data-value="svelte"]').click()
        combobox.querySelector('[data-slot="combobox-item"][data-value="next"]').click()
        combobox.querySelector('[data-slot="combobox-item"][data-value="svelte"]').click()

        expect(combobox.value).toEqual(['next'])
        expect(combobox.isOpen).toBe(true)
        expect(seen).toEqual(['["svelte"]', '["svelte","next"]', '["next"]'])
    })

    it('removes chips with their button or Backspace', () => {
        const combobox = multiple('data-value=\'["next","svelte","astro"]\'')

        combobox.querySelector('[data-combobox-chip-remove]').click()
        expect(combobox.value).toEqual(['svelte', 'astro'])

        key(combobox, 'Backspace')
        expect(combobox.value).toEqual(['svelte'])
    })

    it('writes hidden inputs for form posts', () => {
        const combobox = multiple('data-name="tags" data-value=\'["next","astro"]\'')

        expect([...combobox.querySelectorAll(':scope > input[type="hidden"]')].map(input => `${input.name}=${input.value}`)).toEqual(['tags[]=next', 'tags[]=astro'])
    })
})

describe('autocomplete', () => {
    function autocomplete(attributes = '') {
        document.body.innerHTML = `
            <eso-combobox data-autocomplete ${attributes}>
                <div data-slot="input-group"><input data-combobox-input><button data-combobox-clear></button></div>
                <div data-slot="combobox-content" popover="manual">
                    <div data-slot="combobox-list">
                        <div data-slot="combobox-item" data-value="Hangzhou">Hangzhou</div>
                        <div data-slot="combobox-item" data-value="Shanghai">Shanghai</div>
                        <div data-slot="combobox-item" data-value="Shenzhen">Shenzhen</div>
                    </div>
                </div>
            </eso-combobox>`

        return document.querySelector('eso-combobox')
    }

    const changes = combobox => {
        const seen = []
        combobox.addEventListener('change', event => event.target === combobox && seen.push(combobox.value))
        return seen
    }

    it('takes the typed text as its value, suggesting what matches', () => {
        const combobox = autocomplete()
        const inputs = announced(combobox)

        type(combobox, 'sh')

        expect(combobox.value).toBe('sh')
        expect(inputs).toEqual(['"sh"'])
        expect(combobox.isOpen).toBe(true)
        expect(visible(combobox)).toEqual(['Shanghai', 'Shenzhen'])
        expect(highlighted(combobox)).toBe(undefined)
    })

    it('closes when nothing matches, keeping the text', () => {
        const combobox = autocomplete()

        type(combobox, 'Beijing')

        expect(combobox.isOpen).toBe(false)
        expect(combobox.value).toBe('Beijing')
        expect(combobox.input.value).toBe('Beijing')
    })

    it('fills in a chosen suggestion, then settles it with change', () => {
        const combobox = autocomplete()
        const settled = changes(combobox)

        type(combobox, 'sh')
        key(combobox, 'ArrowDown')
        key(combobox, 'ArrowDown')
        key(combobox, 'Enter')

        expect(combobox.value).toBe('Shenzhen')
        expect(combobox.input.value).toBe('Shenzhen')
        expect(combobox.isOpen).toBe(false)
        expect(settled).toEqual(['Shenzhen'])
    })

    it('settles the typed text on Enter without a highlight, letting the form have it', () => {
        const combobox = autocomplete()
        const settled = changes(combobox)

        type(combobox, 'sh')
        const enter = new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true })
        combobox.input.dispatchEvent(enter)

        expect(enter.defaultPrevented).toBe(false)
        expect(combobox.isOpen).toBe(false)
        expect(settled).toEqual(['sh'])
    })

    it('settles on leaving the input, once per change', async () => {
        const combobox = autocomplete()
        const settled = changes(combobox)

        combobox.input.focus()
        type(combobox, 'Hang')
        combobox.input.blur()
        await Promise.resolve()
        combobox.input.focus()
        combobox.input.blur()
        await Promise.resolve()

        expect(settled).toEqual(['Hang'])
    })

    it('shows the value Livewire sets, and suggests what a morph brings while typing', async () => {
        const combobox = autocomplete('data-should-filter="false"')
        const settled = changes(combobox)

        combobox.value = 'Suzhou'
        expect(combobox.input.value).toBe('Suzhou')
        expect(settled).toEqual([])

        combobox.querySelector('[data-slot="combobox-list"]').innerHTML = ''
        combobox.input.focus()
        type(combobox, 'su')
        expect(combobox.isOpen).toBe(false)

        combobox.querySelector('[data-slot="combobox-list"]').innerHTML = '<div data-slot="combobox-item" data-value="Suzhou">Suzhou</div>'
        await Promise.resolve()

        expect(combobox.isOpen).toBe(true)
        expect(visible(combobox)).toEqual(['Suzhou'])
    })

    it('clears to an empty text', () => {
        const combobox = autocomplete('data-value=\'"Hangzhou"\'')

        expect(combobox.input.value).toBe('Hangzhou')

        combobox.querySelector('[data-combobox-clear]').click()

        expect(combobox.value).toBe('')
        expect(combobox.input.value).toBe('')
    })
})

describe('with a trigger', () => {
    function triggered() {
        document.body.innerHTML = `
            <eso-combobox data-value='"svelte"'>
                <button data-combobox-trigger><span data-slot="combobox-value" data-placeholder="Pick a framework">Pick a framework</span></button>
                <div data-slot="combobox-content" popover="manual">
                    <div data-slot="input-group"><input data-combobox-input></div>
                    <div data-slot="combobox-list">${items}</div>
                </div>
            </eso-combobox>`

        return document.querySelector('eso-combobox')
    }

    it('shows the choice on the trigger and searches with an empty input inside the list', () => {
        const element = triggered()
        const trigger = element.querySelector('[data-combobox-trigger]')

        expect(element.querySelector('[data-slot="combobox-value"]').textContent).toBe('SvelteKit')
        expect(element.input.value).toBe('')

        trigger.click()
        expect(element.isOpen).toBe(true)
        expect(document.activeElement).toBe(element.input)

        element.choose(element.querySelector('[data-value="astro"]'))

        expect(element.value).toBe('astro')
        expect(element.isOpen).toBe(false)
        expect(element.input.value).toBe('')
        expect(element.querySelector('[data-slot="combobox-value"]').textContent).toBe('Astro')
        expect(document.activeElement).toBe(trigger)
    })

    it('shows the placeholder while nothing is chosen', () => {
        const element = triggered()

        element.value = null

        expect(element.querySelector('[data-slot="combobox-value"]').textContent).toBe('Pick a framework')
    })
})
