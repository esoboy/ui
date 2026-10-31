/*
 * <eso-file-upload>
 *
 * Livewire uploads the file input behind wire:model; this adds what a drop area needs
 * around it: dropping files in, and showing them until the upload is done (a chip per
 * file with the progress for File Upload, the new picture for Image Upload). The
 * server's list of uploaded files takes over once Livewire re-renders.
 */

import { connected, define, disconnected } from '../support/element.js'
import { clone, readTemplates } from '../support/templates.js'

export function formatSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    const power = bytes > 0 ? Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1) : 0

    return power === 0 ? `${bytes} B` : `${Number((bytes / 1024 ** power).toFixed(1))} ${units[power]}`
}

/** Whether a file matches an accept attribute such as "image/*,.pdf". */
export function accepts(accept, file) {
    if (! accept) return true

    return accept.split(',').map(rule => rule.trim().toLowerCase()).filter(Boolean).some(rule => {
        if (rule.startsWith('.')) return file.name.toLowerCase().endsWith(rule)
        if (rule.endsWith('/*')) return file.type.startsWith(rule.slice(0, -1))

        return file.type === rule
    })
}

export class FileUploadElement extends HTMLElement {
    #urls = []
    #templates = {}

    connectedCallback() {
        const signal = connected(this)

        this.#templates = readTemplates(this)

        const zone = this.dropzone

        if (! zone) return

        zone.addEventListener('dragenter', event => this.#onDrag(event), { signal })
        zone.addEventListener('dragover', event => this.#onDrag(event), { signal })
        zone.addEventListener('dragleave', event => ! zone.contains(event.relatedTarget) && zone.removeAttribute('data-dragging'), { signal })
        zone.addEventListener('drop', event => this.#onDrop(event), { signal })

        this.addEventListener('livewire-upload-start', () => this.#start(), { signal })
        this.addEventListener('livewire-upload-progress', event => this.#progress(event.detail?.progress ?? 0), { signal })
        for (const type of ['livewire-upload-finish', 'livewire-upload-error', 'livewire-upload-cancel']) {
            this.addEventListener(type, () => this.#end(), { signal })
        }
    }

    disconnectedCallback() {
        disconnected(this)
        this.#revoke()
    }

    get input() {
        return this.querySelector('[data-slot="file-upload-input"]')
    }

    get dropzone() {
        return this.input?.closest('label')
    }

    #onDrag(event) {
        if (this.input?.disabled || ! event.dataTransfer?.types?.includes('Files')) return

        event.preventDefault()
        event.dataTransfer.dropEffect = 'copy'
        this.dropzone.setAttribute('data-dragging', '')
    }

    // Dropped files go into the input, whose change event Livewire uploads.
    #onDrop(event) {
        const input = this.input

        this.dropzone.removeAttribute('data-dragging')

        if (! input || input.disabled || ! event.dataTransfer?.files?.length) return

        event.preventDefault()

        let files = [...event.dataTransfer.files].filter(file => accepts(input.accept, file))

        if (! input.multiple) files = files.slice(0, 1)
        if (! files.length) return

        const transfer = new DataTransfer()

        files.forEach(file => transfer.items.add(file))
        input.files = transfer.files
        input.dispatchEvent(new Event('change', { bubbles: true }))
    }

    #start() {
        const files = [...(this.input?.files ?? [])]

        this.setAttribute('data-uploading', '')
        this.#revoke()

        // Image Upload: show the new picture straight away.
        const image = this.querySelector('[data-slot="image-upload-image"]')

        if (image && files[0]?.type.startsWith('image/')) {
            image.src = this.#url(files[0])
            image.hidden = false
            this.querySelector('[data-slot="image-upload-placeholder"]')?.setAttribute('hidden', '')
        }

        // File Upload: a chip per file, with the progress.
        const pending = this.querySelector('[data-file-upload-pending]')

        if (pending) {
            pending.replaceChildren(...files.flatMap(file => this.#chip(file)))
            pending.closest('[data-file-upload-list]')?.removeAttribute('hidden')
        }

        this.querySelector('[data-slot="file-upload-progress"]')?.removeAttribute('hidden')
        this.#progress(0)
    }

    #progress(percent) {
        this.style.setProperty('--file-upload-progress', `${percent}%`)

        const label = (this.dataset.uploadingLabel ?? '').replace(':progress', percent)

        this.querySelectorAll('[data-file-upload-pending] [data-slot="attachment-description"]').forEach(description => (description.textContent = label))
    }

    #end() {
        this.removeAttribute('data-uploading')
        this.querySelector('[data-slot="file-upload-progress"]')?.setAttribute('hidden', '')

        // Livewire's re-render brings the uploaded files; drop the stand-ins once it has.
        queueMicrotask(() => {
            const pending = this.querySelector('[data-file-upload-pending]')

            pending?.replaceChildren()

            const list = pending?.closest('[data-file-upload-list]')

            if (list && ! list.querySelector('[data-slot="attachment"]')) list.setAttribute('hidden', '')
        })
    }

    #chip(file) {
        const image = file.type.startsWith('image/')
        const chip = clone(this.#templates[image ? 'pending-image' : 'pending'])

        if (! chip) return []

        if (image) chip.querySelector('img').src = this.#url(file)
        chip.querySelector('[data-slot="attachment-title"]').textContent = file.name
        chip.querySelector('[data-slot="attachment-description"]').textContent = formatSize(file.size)

        return [chip]
    }

    #url(file) {
        const url = URL.createObjectURL(file)

        this.#urls.push(url)

        return url
    }

    #revoke() {
        this.#urls.forEach(url => URL.revokeObjectURL(url))
        this.#urls = []
    }
}

export function register() {
    define('eso-file-upload', FileUploadElement)
}
