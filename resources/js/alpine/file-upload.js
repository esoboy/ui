/*
 * <eso-file-upload>
 *
 * Livewire uploads the file input behind wire:model; this adds what a drop area needs
 * around it: dropping files in, and showing them until the upload is done (a chip per
 * file with the progress for File Upload, the new picture for Image Upload). The
 * server's list of uploaded files takes over once Livewire re-renders.
 */

import { disconnected, listen, emit } from './interaction.js'
/** Whether a file matches an accept attribute such as "image/*,.pdf". */
export function accepts(accept, file) {
    if (! accept) return true

    return accept.split(',').map(rule => rule.trim().toLowerCase()).filter(Boolean).some(rule => {
        if (rule.startsWith('.')) return file.name.toLowerCase().endsWith(rule)
        if (rule.endsWith('/*')) return file.type.startsWith(rule.slice(0, -1))

        return file.type === rule
    })
}

export default () => ({
    _urls: null,
    _operation: 0,
    _active: false,
    _progressTimer: null,
    _previousImage: null,
    init() {
        this._urls = []

        const zone = this.dropzone

        if (! zone) return

        listen(this, zone, 'dragenter', event => this._onDrag(event), {})
        listen(this, zone, 'dragover', event => this._onDrag(event), {})
        listen(this, zone, 'dragleave', event => ! zone.contains(event.relatedTarget) && zone.removeAttribute('data-dragging'), {})
        listen(this, zone, 'drop', event => this._onDrop(event), {})
        listen(this, zone, 'click', event => {
            if (! event.target.closest('label') && ! this.input?.disabled) this.input?.click()
        }, {})

        listen(this, this.$el, 'livewire-upload-start', () => this._start(), {})
        listen(this, this.$el, 'livewire-upload-progress', event => this._progress(event.detail?.progress ?? 0), {})
        for (const type of ['livewire-upload-finish', 'livewire-upload-error', 'livewire-upload-cancel']) {
            listen(this, this.$el, type, () => this._end(type.replace('livewire-upload-', '')), {})
        }

    },
    destroy() {
        disconnected(this)
        clearTimeout(this._progressTimer)
        this._revoke()
    },
    get input() {
        return this.$el.querySelector('[data-slot="file-upload-input"]')
    },

    get dropzone() {
        return this.input?.closest('[data-slot="file-upload-dropzone"], [data-slot="image-upload-dropzone"]')
    },

    _onDrag(event) {
        if (this.input?.disabled || ! event.dataTransfer?.types?.includes('Files')) return

        event.preventDefault()
        event.dataTransfer.dropEffect = 'copy'
        this.dropzone.setAttribute('data-dragging', '')
    },

    _onDrop(event) {
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
        emit(input, 'change')
    },

    get uploadState() {
        return this.$el.querySelector('[data-upload-state]')
    },

    _start() {
        const files = [...(this.input?.files ?? [])]
        this._operation++
        this._active = true
        clearTimeout(this._progressTimer)
        this.$el.setAttribute('data-uploading', '')
        this._revoke()

        const image = this.$el.querySelector('[data-slot="image-upload-image"]')
        if (image && files[0]?.type.startsWith('image/')) {
            this._previousImage = { src: image.getAttribute('src'), hidden: image.hidden }
            image.src = this._url(files[0])
            image.hidden = false
            this.$el.querySelector('[data-slot="image-upload-placeholder"]')?.setAttribute('hidden', '')
        }

        if (!this.uploadState) return
        emit(this.uploadState, 'ui-upload-start', {
            operation: this._operation,
            files: files.map(file => ({
                name: file.name,
                size: file.size,
                preview: !image && file.type.startsWith('image/') ? this._url(file) : null,
            })),
        })
    },

    _progress(percent) {
        if (!this._active) return
        clearTimeout(this._progressTimer)
        this._progressTimer = setTimeout(() => {
            if (!this.uploadState) return
            emit(this.uploadState, 'ui-upload-progress', { operation: this._operation, progress: Math.round(percent) })
        }, 100)
    },

    _end(phase) {
        if (!this._active) return
        this._active = false
        clearTimeout(this._progressTimer)
        this.$el.removeAttribute('data-uploading')
        if (this.uploadState) emit(this.uploadState, 'ui-upload-end', { operation: this._operation, phase })

        if (phase !== 'finish' && this._previousImage) {
            const image = this.$el.querySelector('[data-slot="image-upload-image"]')
            if (image) {
                image.src = this._previousImage.src ?? ''
                image.hidden = this._previousImage.hidden
                this.$el.querySelector('[data-slot="image-upload-placeholder"]')?.toggleAttribute('hidden', !image.hidden)
            }
        }
        this._previousImage = null
    },

    _url(file) {
        const url = URL.createObjectURL(file)

        this._urls.push(url)

        return url
    },

    _revoke() {
        this._urls.forEach(url => URL.revokeObjectURL(url))
        this._urls = []
    },
})
