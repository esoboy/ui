import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { accepts, formatSize, register as registerFileUpload } from '../../resources/js/components/file-upload.js'

beforeAll(() => {
    registerFileUpload()
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
    document.body.innerHTML = ''
})

describe('helpers', () => {
    it('formats sizes like the server', () => {
        expect(formatSize(0)).toBe('0 B')
        expect(formatSize(2048)).toBe('2 KB')
        expect(formatSize(1258291)).toBe('1.2 MB')
    })

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
                <template data-template="pending">
                    <div data-slot="attachment" data-state="uploading">
                        <div data-slot="attachment-media" data-variant="icon"><svg data-icon-name="file"></svg></div>
                        <div data-slot="attachment-content"><span data-slot="attachment-title"></span><span data-slot="attachment-description"></span></div>
                    </div>
                </template>
                <template data-template="pending-image">
                    <div data-slot="attachment" data-state="uploading">
                        <div data-slot="attachment-media" data-variant="image"><img alt=""></div>
                        <div data-slot="attachment-content"><span data-slot="attachment-title"></span><span data-slot="attachment-description"></span></div>
                    </div>
                </template>
                <label data-slot="file-upload-dropzone">
                    <input type="file" data-slot="file-upload-input" ${input}>
                    <span data-slot="file-upload-progress" hidden></span>
                </label>
                <div data-file-upload-list hidden><div data-file-upload-pending></div></div>
            </eso-file-upload>`

        const element = document.querySelector('eso-file-upload')

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

    it('shows a chip per file with the progress while uploading', () => {
        const element = mount()

        element.input.files = files()
        element.input.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))

        const chips = [...element.querySelectorAll('[data-file-upload-pending] [data-slot="attachment"]')]

        expect(element.hasAttribute('data-uploading')).toBe(true)
        expect(element.querySelector('[data-file-upload-list]').hidden).toBe(false)
        expect(chips.map(chip => chip.dataset.state)).toEqual(['uploading', 'uploading'])
        expect(chips[0].querySelector('img').getAttribute('src')).toMatch(/^blob:/)
        expect(chips.map(chip => chip.querySelector('[data-slot="attachment-title"]').textContent)).toEqual(['cover.png', 'notes.txt'])
        expect(chips[1].querySelector('[data-slot="attachment-media"]').dataset.variant).toBe('icon')
        expect(chips[1].querySelector('[data-slot="attachment-media"] svg').dataset.iconName).toBe('file')

        element.input.dispatchEvent(new CustomEvent('livewire-upload-progress', { bubbles: true, detail: { progress: 42 } }))

        expect(element.style.getPropertyValue('--file-upload-progress')).toBe('42%')
        expect(chips[1].querySelector('[data-slot="attachment-description"]').textContent).toBe('Uploading 42%')
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
                <label><input type="file" data-slot="file-upload-input"><img data-slot="image-upload-image" hidden><span data-slot="image-upload-placeholder"></span></label>
            </eso-file-upload>`

        const element = document.querySelector('eso-file-upload')

        Object.defineProperty(element.input, 'files', { value: [new File(['x'], 'me.png', { type: 'image/png' })] })
        element.input.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))

        expect(element.querySelector('img').hidden).toBe(false)
        expect(element.querySelector('img').getAttribute('src')).toMatch(/^blob:/)
        expect(element.querySelector('[data-slot="image-upload-placeholder"]').hidden).toBe(true)
    })
})
