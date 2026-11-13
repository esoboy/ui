import { engineDriver, cleanupControllers } from './alpine-harness.js'
import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import createFileUpload, { accepts } from '../../resources/js/alpine/file-upload.js'

beforeAll(() => {
    URL.createObjectURL ||= () => 'blob:preview'
    URL.revokeObjectURL ||= () => {}

    // jsdom has no DataTransfer; a list that FileList-accepting code can take.
    globalThis.DataTransfer ||= class {
        #files = []
        items = { add: file => this.#files.push(file) }
        get files() { return this.#files }
    }
})

afterEach(() => {
    cleanupControllers()
    document.body.innerHTML = ''
})

describe('helpers', () => {
    it('matches accept rules by extension, wildcard and type', () => {
        const png = new File([''], 'a.PNG', { type: 'image/png' })
        const pdf = new File([''], 'b.pdf', { type: 'application/pdf' })

        expect(accepts('', png)).toBe(true)
        expect(accepts('image/*', png)).toBe(true)
        expect(accepts('image/*', pdf)).toBe(false)
        expect(accepts('.png, application/pdf', png)).toBe(true)
        expect(accepts('.png, application/pdf', pdf)).toBe(true)
        expect(accepts('video/mp4', pdf)).toBe(false)
    })
})

describe('file upload', () => {
    function mount(input = 'multiple') {
        document.body.innerHTML = `
            <eso-file-upload data-uploading-label="Uploading :progress%">
                <div data-slot="file-upload-dropzone">
                    <label><input type="file" data-slot="file-upload-input" ${input}></label>
                    <div data-upload-state="idle"></div>
                </div>
                <div data-file-upload-list hidden><div data-file-upload-pending></div></div>
            </eso-file-upload>`

        const element = engineDriver(createFileUpload, document.querySelector('eso-file-upload'))

        // jsdom refuses to assign a plain list to input.files; keep what was dropped.
        let files = []
        Object.defineProperty(element.input, 'files', { get: () => files, set: value => (files = [...value]) })

        return element
    }

    const files = () => [new File(['x'], 'cover.png', { type: 'image/png' }), new File(['yy'], 'notes.txt', { type: 'text/plain' })]

    const drag = (element, type, dropped = files()) => {
        const event = new Event(type, { bubbles: true, cancelable: true })

        event.dataTransfer = { types: ['Files'], files: dropped, dropEffect: '' }
        element.dropzone.dispatchEvent(event)

        return event
    }

    it('highlights while files are dragged over, and drops them into the input', () => {
        const element = mount('multiple accept="image/*"')
        const changed = vi.fn()

        element.input.addEventListener('change', changed)

        expect(drag(element, 'dragover').defaultPrevented).toBe(true)
        expect(element.dropzone.hasAttribute('data-dragging')).toBe(true)

        drag(element, 'drop')

        expect(element.dropzone.hasAttribute('data-dragging')).toBe(false)
        expect(element.input.files.map(file => file.name)).toEqual(['cover.png'])
        expect(changed).toHaveBeenCalledTimes(1)
    })

    it('keeps one file for a single input, and none when disabled', () => {
        const single = mount('')

        drag(single, 'drop')
        expect(single.input.files).toHaveLength(1)

        const disabled = mount('disabled')

        expect(drag(disabled, 'dragover').defaultPrevented).toBe(false)
    })

    it('opens the file input from the dropzone padding but not when disabled', () => {
        const element = mount()
        const open = vi.spyOn(element.input, 'click').mockImplementation(() => {})

        element.dropzone.click()
        expect(open).toHaveBeenCalledTimes(1)

        element.input.disabled = true
        element.dropzone.click()
        expect(open).toHaveBeenCalledTimes(1)
    })

    it('hands file metadata to the pending upload component and reports cancellation', () => {
        const element = mount()
        const state = element.querySelector('[data-upload-state]')
        const start = vi.fn()
        const end = vi.fn()
        state.addEventListener('ui-upload-start', event => start(event.detail))
        state.addEventListener('ui-upload-end', event => end(event.detail))

        element.input.files = files()
        element.input.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))
        expect(start).toHaveBeenCalledWith({
            operation: 1,
            files: [
                { name: 'cover.png', size: 1, preview: expect.stringMatching(/^blob:/) },
                { name: 'notes.txt', size: 2, preview: null },
            ],
        })
        expect(element.hasAttribute('data-uploading')).toBe(true)

        element.input.dispatchEvent(new CustomEvent('livewire-upload-cancel', { bubbles: true }))
        expect(end).toHaveBeenCalledWith({ operation: 1, phase: 'cancel' })
        expect(element.hasAttribute('data-uploading')).toBe(false)
    })

    it('clears the stand-ins once the upload is done', async () => {
        const element = mount()

        element.input.files = files()
        element.input.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))
        element.input.dispatchEvent(new CustomEvent('livewire-upload-finish', { bubbles: true }))
        await Promise.resolve()

        expect(element.hasAttribute('data-uploading')).toBe(false)
        expect(element.querySelectorAll('[data-file-upload-pending] *')).toHaveLength(0)
        expect(element.querySelector('[data-file-upload-list]').hidden).toBe(true)
    })
})

describe('image upload', () => {
    it('shows the new picture before the upload ends', () => {
        document.body.innerHTML = `
            <eso-file-upload data-slot="image-upload">
                <label data-slot="image-upload-dropzone"><input type="file" data-slot="file-upload-input"><img data-slot="image-upload-image" hidden><span data-slot="image-upload-placeholder"></span></label>
            </eso-file-upload>`

        const element = engineDriver(createFileUpload, document.querySelector('eso-file-upload'))

        Object.defineProperty(element.input, 'files', { value: [new File(['x'], 'me.png', { type: 'image/png' })] })
        element.input.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))

        expect(element.querySelector('img').hidden).toBe(false)
        expect(element.querySelector('img').getAttribute('src')).toMatch(/^blob:/)
        expect(element.querySelector('[data-slot="image-upload-placeholder"]').hidden).toBe(true)
    })
})
