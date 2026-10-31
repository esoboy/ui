import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { codeOf, register as registerCodeBlock } from '../../resources/js/components/code-block.js'
import { register as registerQuestionnaire } from '../../resources/js/components/questionnaire.js'
import { register as registerRadioGroup } from '../../resources/js/components/radio-group.js'

beforeAll(() => {
    registerRadioGroup()
    registerCodeBlock()
    registerQuestionnaire()
})

afterEach(() => {
    document.body.innerHTML = ''
})

const tick = () => new Promise(resolve => setTimeout(resolve, 0))

describe('code block', () => {
    const block = `
        <div data-slot="code-block">
            <pre data-slot="code-block-content"><code data-slot="code-block-code"><span data-slot="code-block-line"><span data-slot="code-block-line-content">a</span></span><span data-slot="code-block-line"><span data-slot="code-block-line-content">\n</span></span><span data-slot="code-block-line"><span data-slot="code-block-line-content">b</span></span></code></pre>
            <button data-code-block-copy data-label-copy="Copy" data-label-copied="Copied" aria-label="Copy"></button><span data-code-block-status></span>
        </div>`

    it('reads the code one line per row, blank lines included', () => {
        document.body.innerHTML = block

        expect(codeOf(document.querySelector('[data-slot="code-block"]'))).toBe('a\n\nb')
    })

    it('copies, shows the check and announces it for two seconds', async () => {
        vi.useFakeTimers()
        const writeText = vi.fn(() => Promise.resolve())
        Object.defineProperty(navigator, 'clipboard', { value: { writeText }, configurable: true })
        document.body.innerHTML = block

        const button = document.querySelector('[data-code-block-copy]')

        button.click()
        await vi.waitFor(() => expect(button.hasAttribute('data-copied')).toBe(true))

        expect(writeText).toHaveBeenCalledWith('a\n\nb')
        expect(button.getAttribute('aria-label')).toBe('Copied')
        expect(document.querySelector('[data-code-block-status]').textContent).toBe('Copied')

        vi.advanceTimersByTime(2000)
        expect(button.hasAttribute('data-copied')).toBe(false)
        expect(button.getAttribute('aria-label')).toBe('Copy')
        vi.useRealTimers()
    })

    it('copies a given value instead', async () => {
        const writeText = vi.fn(() => Promise.resolve())
        Object.defineProperty(navigator, 'clipboard', { value: { writeText }, configurable: true })
        document.body.innerHTML = '<button data-code-block-copy data-value="npm i shiki"></button>'

        document.querySelector('button').click()

        expect(writeText).toHaveBeenCalledWith('npm i shiki')
    })
})

describe('questionnaire', () => {
    function mount(attributes = '') {
        document.body.innerHTML = `
            <form data-slot="questionnaire" novalidate ${attributes}>
                <div data-slot="questionnaire-progress" data-template="Question :current of :total"></div>
                <fieldset data-slot="questionnaire-item" data-name="direction" data-required tabindex="-1">
                    <p data-slot="questionnaire-description">Choose one.</p>
                    <eso-radio-group name="direction">
                        <label data-slot="questionnaire-choice"><input type="radio" value="a" data-slot="questionnaire-choice-input"><span data-slot="questionnaire-choice-shortcut" hidden></span></label>
                        <label data-slot="questionnaire-choice"><input type="radio" value="b" data-slot="questionnaire-choice-input"><span data-slot="questionnaire-choice-shortcut" hidden></span></label>
                        <input type="text" name="direction" data-slot="questionnaire-input">
                    </eso-radio-group>
                    <p data-slot="questionnaire-error" hidden>Choose an answer to continue.</p>
                </fieldset>
                <fieldset data-slot="questionnaire-item" data-name="detail" data-multiple tabindex="-1">
                    <label data-slot="questionnaire-choice"><input type="checkbox" value="x" data-slot="questionnaire-choice-input"></label>
                    <p data-slot="questionnaire-error" hidden>Choose an answer or skip this question.</p>
                </fieldset>
                <fieldset data-slot="questionnaire-item" data-name="never" disabled tabindex="-1"></fieldset>
                <button type="button" data-questionnaire="previous"></button>
                <button type="button" data-questionnaire="skip"></button>
                <button type="button" data-questionnaire="next"></button>
                <button type="submit" data-questionnaire="submit"></button>
            </form>`

        return document.querySelector('form')
    }

    const active = form => form.querySelector('[data-slot="questionnaire-item"][data-active]')?.dataset.name
    const shown = form => [...form.querySelectorAll('[data-questionnaire]')].filter(button => ! button.hidden).map(button => button.dataset.questionnaire)
    const click = (form, action) => form.querySelector(`[data-questionnaire="${action}"]`).click()
    const press = (target, key, init = {}) => target.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true, ...init }))

    it('shows the first enabled item with its progress and actions', async () => {
        const form = mount()
        await tick()

        expect(active(form)).toBe('direction')
        expect(form.querySelector('[data-name="detail"]').hidden).toBe(true)
        expect(form.querySelector('[data-name="detail"]').hasAttribute('inert')).toBe(true)
        expect(form.querySelector('[data-slot="questionnaire-progress"]').textContent).toBe('Question 1 of 2')
        expect(shown(form)).toEqual(['next'])
    })

    it('needs an answer before going on, then moves to the next item', async () => {
        const form = mount()
        await tick()

        click(form, 'next')
        const item = form.querySelector('[data-name="direction"]')

        expect(active(form)).toBe('direction')
        expect(item.getAttribute('aria-invalid')).toBe('true')
        expect(item.querySelector('[data-slot="questionnaire-error"]').hidden).toBe(false)
        expect(item.getAttribute('aria-describedby').split(' ')).toContain(item.querySelector('[data-slot="questionnaire-error"]').id)

        item.querySelector('[value="b"]').click()
        expect(item.hasAttribute('aria-invalid')).toBe(false)

        click(form, 'next')
        expect(active(form)).toBe('detail')
        expect(shown(form)).toEqual(['previous', 'skip', 'submit'])
        expect(form.querySelector('[data-slot="questionnaire-progress"]').textContent).toBe('Question 2 of 2')

        click(form, 'previous')
        expect(active(form)).toBe('direction')
    })

    it('lets a freeform answer replace the choice in a single item', async () => {
        const form = mount()
        await tick()

        const radio = form.querySelector('[value="a"]')
        const text = form.querySelector('[data-slot="questionnaire-input"]')

        radio.click()
        text.value = 'Something else'
        text.dispatchEvent(new Event('input', { bubbles: true }))
        expect(radio.checked).toBe(false)

        radio.click()
        expect(text.value).toBe('')
    })

    it('skips an optional item, submitting when it is the last', async () => {
        const form = mount()
        const submitted = vi.fn(event => event.preventDefault())
        form.addEventListener('submit', submitted)
        form.requestSubmit = () => form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }))
        await tick()

        form.querySelector('[value="a"]').click()
        click(form, 'next')
        click(form, 'skip')
        await tick()

        expect(submitted).toHaveBeenCalledTimes(1)
    })

    it('stops a submit while an item needs an answer, and shows that item', async () => {
        const form = mount()
        const later = vi.fn()
        form.addEventListener('submit', later)
        await tick()

        const event = new Event('submit', { bubbles: true, cancelable: true })
        form.dispatchEvent(event)

        expect(event.defaultPrevented).toBe(true)
        expect(later).not.toHaveBeenCalled()
        expect(active(form)).toBe('direction')
        expect(form.querySelector('[data-name="direction"]').getAttribute('aria-invalid')).toBe('true')
    })

    it('picks choices by shortcut, but not while typing', async () => {
        const form = mount('data-shortcuts="letters"')
        await tick()

        const item = form.querySelector('[data-name="direction"]')

        expect([...item.querySelectorAll('[data-slot="questionnaire-choice-shortcut"]')].map(badge => badge.textContent)).toEqual(['A', 'B'])

        press(form.querySelector('[data-slot="questionnaire-input"]'), 'b')
        expect(item.querySelector('[value="b"]').checked).toBe(false)

        press(item, 'b')
        expect(item.querySelector('[value="b"]').checked).toBe(true)
    })

    it('goes on with Enter on an answer and Ctrl+Enter anywhere', async () => {
        const form = mount()
        await tick()

        const radio = form.querySelector('[value="a"]')

        radio.click()
        press(radio, 'Enter')
        expect(active(form)).toBe('detail')

        click(form, 'previous')
        press(form.querySelector('[data-name="direction"]'), 'Enter', { ctrlKey: true })
        expect(active(form)).toBe('detail')
    })

    it('keeps the active item through a morph', async () => {
        const form = mount()
        await tick()

        form.querySelector('[value="a"]').click()
        click(form, 'next')

        // A morph puts back the server's markup: first item shown, second hidden.
        const [first, second] = form.querySelectorAll('[data-slot="questionnaire-item"]')
        first.removeAttribute('hidden')
        second.setAttribute('hidden', '')
        await tick()

        expect(first.hidden).toBe(true)
        expect(second.hidden).toBe(false)
    })
})
