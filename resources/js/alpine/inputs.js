import { emit, notifyModel } from './interaction.js'

export function numberInput() {
    return {
        timer: null,
        holding: false,
        repeated: false,

        notifyModel() { notifyModel(this) },
        blurModel() { emit(this.$el, 'blur') },

        press(event) {
            const button = event.target.closest?.('[data-number-input]')
            if (!button || button.disabled || event.button !== 0) return
            event.preventDefault()
            this.$el.querySelector('input[role="spinbutton"]')?.focus()
            this.stop()
            this.holding = true
            this.repeated = false
            const direction = button.dataset.numberInput === 'increment' ? 1 : -1
            const repeat = async () => {
                if (!this.holding || button.disabled) return
                this.repeated = true
                await this.$wire.step(direction)
                if (this.holding) this.timer = setTimeout(repeat, 100)
            }
            this.timer = setTimeout(repeat, 400)
        },

        afterRepeat(event) {
            if (!this.repeated || !event.target.closest?.('[data-number-input]')) return
            this.repeated = false
            event.preventDefault()
            event.stopImmediatePropagation()
        },

        stop() {
            this.holding = false
            clearTimeout(this.timer)
        },

        destroy() { this.stop() },
    }
}

export function rating() {
    return {
        notifyModel() { notifyModel(this) },
        blurModel() { emit(this.$el, 'blur') },

        key(event) {
            const buttons = [...this.$el.querySelectorAll('[data-slot="rating-item"]:not(:disabled)')]
            const index = buttons.indexOf(event.target)
            if (index < 0) return
            const positions = {
                ArrowRight: index + 1,
                ArrowUp: index + 1,
                ArrowLeft: index - 1,
                ArrowDown: index - 1,
                Home: 0,
                End: buttons.length - 1,
            }
            const position = positions[event.key]
            if (position === undefined) return

            event.preventDefault()
            const button = buttons[Math.max(0, Math.min(position, buttons.length - 1))]
            button.focus()
            this.$wire.choose(Number(button.dataset.value), false)
        },
    }
}

export function slider(binding) {
    return {
        drag: null,
        stopMorph: null,
        get thumbs() { return [...this.$el.querySelectorAll('[data-slot="slider-thumb"]')] },

        init() {
            if (binding.property) {
                this.$watch(`$wire.$get(${JSON.stringify(binding.property)})`, () => this.paint())
                this.stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
                    if (el.contains(this.$el)) this.paint()
                })
            }
            this.$nextTick(() => this.paint())
        },

        destroy() { this.stopMorph?.() },

        paint(thumbs = this.thumbs) {
            if (! thumbs.length) return
            const min = Number(thumbs[0].min), max = Number(thumbs[0].max)
            const drag = this.drag
            const preview = drag?.preview == null ? null : this.constrain(drag.thumb, drag.preview, thumbs)
            const percent = value => max === min ? 0 : (value - min) / (max - min) * 100
            const percentages = thumbs.map(thumb => percent(thumb === drag?.thumb && preview !== null ? preview : thumb.valueAsNumber))
            const range = this.$el.querySelector('[data-slot="slider-range"]')
            range?.style.setProperty('--start', percentages.length > 1 ? Math.min(...percentages) : 0)
            range?.style.setProperty('--end', Math.max(...percentages))

            if (preview !== null) {
                // The input still owns the stepped value. Move its painted thumb by the remainder.
                const direction = drag.vertical || drag.rtl ? -1 : 1
                const offset = direction * drag.length * (percent(preview) - percent(drag.thumb.valueAsNumber)) / 100
                drag.thumb.style.setProperty('--slider-thumb-translate', drag.vertical ? `0 ${offset}px` : `${offset}px`)
            }
        },

        positionAt(event, drag) {
            const { control, vertical, rtl, radius, min, max } = drag
            const rect = control.getBoundingClientRect()
            const length = Math.max(0, (vertical ? rect.height : rect.width) - 2 * radius)
            const position = vertical ? rect.bottom - event.clientY : rtl ? rect.right - event.clientX : event.clientX - rect.left
            const value = length ? min + (position - radius) / length * (max - min) : min
            return { value, length }
        },

        press(event) {
            if (this.drag || event.button !== 0 || event.isPrimary === false) return
            const control = event.target.closest('[data-slot="slider-control"]')
            if (! control) return
            const thumbs = this.thumbs.filter(thumb => ! thumb.disabled)
            if (! thumbs.length) return

            const styles = getComputedStyle(thumbs[0])
            const drag = {
                pointer: event.pointerId,
                control,
                vertical: this.$el.dataset.orientation === 'vertical',
                rtl: styles.direction === 'rtl',
                radius: parseFloat(styles.getPropertyValue('--slider-thumb-size')) / 2 || 0,
                min: Number(thumbs[0].min),
                max: Number(thumbs[0].max),
            }
            const { value } = this.positionAt(event, drag)
            const grabbed = thumbs.includes(event.target)
            const thumb = grabbed ? event.target : thumbs.reduce((nearest, candidate) =>
                Math.abs(candidate.valueAsNumber - value) < Math.abs(nearest.valueAsNumber - value) ? candidate : nearest)
            const matching = thumbs.filter(candidate => candidate.value === thumb.value)

            event.preventDefault()
            event.currentTarget.setPointerCapture?.(event.pointerId)
            this.drag = {
                ...drag,
                thumb,
                start: thumb.valueAsNumber,
                offset: grabbed ? value - thumb.valueAsNumber : 0,
                overlapping: matching.length > 1 ? matching : null,
                preview: null,
            }
            if (! this.drag.overlapping) thumb.focus({ preventScroll: true })
            this.move(event)
        },

        move(event) {
            const drag = this.drag
            if (! drag || drag.pointer !== event.pointerId) return
            const position = this.positionAt(event, drag)
            const value = position.value - drag.offset
            // Keep the choice open until the pointer selects a different step.
            const thumb = drag.overlapping
                ? (value < drag.start ? drag.overlapping[0] : drag.overlapping.at(-1))
                : drag.thumb
            if (thumb.disabled || ! this.$el.contains(thumb)) return

            const thumbs = this.thumbs
            const preview = this.constrain(thumb, value, thumbs)
            if (thumb === drag.thumb && preview === drag.preview && position.length === drag.length) return
            if (thumb !== drag.thumb) drag.thumb.style.removeProperty('--slider-thumb-translate')
            drag.thumb = thumb
            drag.preview = preview
            drag.length = position.length

            const before = thumb.value
            if (preview !== thumb.valueAsNumber) thumb.valueAsNumber = preview
            if (before === thumb.value) {
                this.paint(thumbs)
                return
            }
            if (drag.overlapping) {
                drag.overlapping = null
                thumb.focus({ preventScroll: true })
            }
            emit(thumb, 'input', thumb.valueAsNumber)
        },

        end(event) {
            if (! this.drag || this.drag.pointer !== event.pointerId) return
            if (event.type === 'pointerup') this.move(event)
            const { thumb, start } = this.drag
            this.drag = null
            thumb.style.removeProperty('--slider-thumb-translate')
            this.paint()
            if (! thumb.disabled && this.$el.contains(thumb) && thumb.valueAsNumber !== start) {
                emit(thumb, 'change', thumb.valueAsNumber)
            }
        },

        constrain(thumb, value, thumbs = this.thumbs) {
            const index = thumbs.indexOf(thumb)
            const min = thumbs[index - 1]?.valueAsNumber ?? Number(thumb.min)
            const max = thumbs[index + 1]?.valueAsNumber ?? Number(thumb.max)
            return Math.min(max, Math.max(min, value))
        },

        input({ target: thumb }) {
            const thumbs = this.thumbs
            if (! thumbs.includes(thumb)) return
            // Capture native keyboard input before wire:model reads the value.
            const value = this.constrain(thumb, thumb.valueAsNumber, thumbs)
            if (value !== thumb.valueAsNumber) thumb.valueAsNumber = value
            this.paint(thumbs)
        },
    }
}

export function otp() {
    return {
        stopMorph: null,

        init() {
            this.stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
                if (el === this.$el || el.contains(this.$el)) this.render()
            })
        },

        destroy() { this.stopMorph?.() },

        render() {
            this.$nextTick(() => {
                const input = this.$el.querySelector('[data-slot="input-otp-input"]')
                if (! input) return
                const focused = document.activeElement === input
                const position = Math.min(input.selectionStart ?? input.value.length, input.maxLength - 1)
                for (const slot of this.$el.querySelectorAll('[data-slot="input-otp-slot"]')) {
                    const index = Number(slot.dataset.index)
                    slot.dataset.active = String(focused && index === position)
                    slot.dataset.caret = String(focused && index === position && input.value[index] === undefined)
                }
            })
        },
    }
}

export function inputCount() {
    return {
        timer: null,

        get input() {
            return this.$el.closest('[data-slot="input-group"]')?.querySelector('[data-slot="input-group-control"]')
        },

        init() {
            const input = this.input
            if (!input) return
            const descriptions = (input.getAttribute('aria-describedby') ?? '').split(' ').filter(Boolean)
            if (!descriptions.includes(this.$el.id)) {
                input.setAttribute('aria-describedby', [...descriptions, this.$el.id].join(' '))
            }
        },

        queue(event) {
            if (event.target !== this.input || event.isComposing) return
            clearTimeout(this.timer)
            const value = event.target.value
            this.timer = setTimeout(() => this.$wire.countText(value), 150)
        },

        destroy() { clearTimeout(this.timer) },
    }
}

export function copyCode() {
    return {
        copied: false,
        timer: 0,
        async copy(control = this.$el) {
            const block = this.$el.closest('[data-slot="code-block"]')
            const lines = block?.querySelectorAll('[data-slot="code-block-line-content"]') ?? []
            const code = lines.length ? [...lines].map(line => line.textContent === '\n' ? '' : line.textContent).join('\n') : block?.querySelector('[data-slot="code-block-code"]')?.textContent ?? ''
            try {
                await navigator.clipboard.writeText(control.dataset.value ?? code)
                this.copied = true
                clearTimeout(this.timer)
                this.timer = setTimeout(() => { this.copied = false }, 2000)
            } catch {}
        },
        destroy() { clearTimeout(this.timer) },
    }
}
