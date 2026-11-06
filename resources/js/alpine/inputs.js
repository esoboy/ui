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
        get thumbs() { return [...this.$el.querySelectorAll('[data-slot="slider-thumb"]')] },
        paint() {
            const bound = binding.property ? this.$wire.$get(binding.property) : null
            const numbers = bound === null ? this.thumbs.map(thumb => Number(thumb.value)) : (Array.isArray(bound) ? bound : [bound]).map(Number)
            const first = this.thumbs[0]
            if (! first) return
            const min = Number(first.min || 0), max = Number(first.max || 100)
            const percentages = numbers.map(number => max === min ? 0 : (number - min) / (max - min) * 100)
            const range = this.$el.querySelector('[data-slot="slider-range"]')
            range?.style.setProperty('--start', percentages.length > 1 ? Math.min(...percentages) : 0)
            range?.style.setProperty('--end', Math.max(...percentages))
        },
        valueAt(event) {
            const first = this.thumbs[0]
            const rect = this.$el.querySelector('[data-slot="slider-control"]').getBoundingClientRect()
            const vertical = this.$el.dataset.orientation === 'vertical'
            const fraction = Math.min(1, Math.max(0, vertical ? (rect.bottom - event.clientY) / rect.height : (event.clientX - rect.left) / rect.width))
            return Number(first.min || 0) + fraction * (Number(first.max || 100) - Number(first.min || 0))
        },
        press(event) {
            const thumbs = this.thumbs.filter(thumb => ! thumb.disabled)
            if (event.button !== 0 || ! thumbs.length || thumbs.includes(event.target)) return
            const value = this.valueAt(event)
            const thumb = thumbs.reduce((nearest, candidate) => Math.abs(Number(candidate.value) - value) < Math.abs(Number(nearest.value) - value) ? candidate : nearest)
            event.preventDefault()
            this.drag = { thumb, start: thumb.value }
            thumb.focus()
            event.currentTarget.setPointerCapture?.(event.pointerId)
            this.move(event)
        },
        move(event) {
            if (! this.drag) return
            const thumb = this.drag.thumb
            const before = thumb.value
            thumb.value = String(this.valueAt(event))
            if (before !== thumb.value) emit(thumb, 'input')
            this.paint()
        },
        end() {
            if (! this.drag) return
            const { thumb, start } = this.drag
            this.drag = null
            if (thumb.value !== start) emit(thumb, 'change')
        },
        input(event) {
            const thumbs = this.thumbs
            const index = thumbs.indexOf(event.target)
            if (index < 0) return
            const thumb = thumbs[index], before = thumb.value
            if (index > 0 && Number(thumb.value) < Number(thumbs[index - 1].value)) thumb.value = thumbs[index - 1].value
            if (index < thumbs.length - 1 && Number(thumb.value) > Number(thumbs[index + 1].value)) thumb.value = thumbs[index + 1].value
            if (thumb.value !== before) emit(thumb, 'input')
            this.paint()
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
