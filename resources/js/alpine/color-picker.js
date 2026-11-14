const clamp = number => Math.min(1, Math.max(0, number))
export function hexToHsv(hex) {
    const number = parseInt(hex.slice(1), 16)
    const [r, g, b] = [number >> 16, number >> 8, number].map(channel => (channel & 255) / 255)
    const v = Math.max(r, g, b), delta = v - Math.min(r, g, b)
    const hue = !delta ? 0 : v === r ? ((g - b) / delta) % 6 : v === g ? (b - r) / delta + 2 : (r - g) / delta + 4
    return { h: (hue * 60 + 360) % 360, s: v ? delta / v : 0, v }
}
export function hsvToHex({ h, s, v }) {
    const channel = n => { const k = (n + h / 60) % 6; return v - v * s * Math.max(0, Math.min(k, 4 - k, 1)) }
    return `#${[channel(5), channel(3), channel(1)].map(c => Math.round(c * 255).toString(16).padStart(2, '0')).join('')}`
}
export default () => ({
    hue: 0,
    pointer: null,
    draft: null,
    stopMorph: null,

    init() {
        this.paint()
        this.stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
            if (el === this.$el) this.paint()
        })
    },

    destroy() { this.stopMorph?.() },

    get hsv() {
        if (this.draft) return this.draft
        const hsv = hexToHsv(this.$el.dataset.value || '#000000')
        if (!hsv.s || hsv.h === 0 && this.hue === 360) hsv.h = this.hue
        return hsv
    },

    paint() {
        const hsv = this.hsv
        if (hsv.s) this.hue = hsv.h
        this.$el.style.setProperty('--color-picker-hue', hsv.h)
        const thumb = this.$el.querySelector('[data-slot="color-picker-thumb"]')
        if (thumb) {
            thumb.style.left = `${hsv.s * 100}%`
            thumb.style.top = `${(1 - hsv.v) * 100}%`
            thumb.style.setProperty('--color-picker-color', hsvToHex(hsv))
        }
        this.$el.querySelector('[data-slot="color-picker-area"]')?.setAttribute('aria-valuetext', `${Math.round(hsv.s * 100)}%, ${Math.round(hsv.v * 100)}%`)
        this.$el.querySelector('[data-slot="color-picker-swatch"]')?.style.setProperty('--color-picker-color', this.draft ? hsvToHex(hsv) : this.$el.dataset.value || 'transparent')
        const range = this.$el.querySelector('[data-color-picker-hue] input[type="range"]')
        if (range && Number(range.value) !== Math.round(hsv.h)) {
            range.value = Math.round(hsv.h)
            this.$nextTick(() => window.Alpine.evaluate(range.closest('[data-slot="slider"]'), 'paint()'))
        }
    },

    preview(changes) {
        this.draft = { ...this.hsv, ...changes }
        this.paint()
    },

    commit() {
        if (!this.draft) return
        const draft = this.draft
        this.$wire.setColor(hsvToHex(draft)).finally(() => {
            if (this.draft === draft) this.cancel()
        })
    },

    previewHue(event) {
        if (!event.target.matches('[data-color-picker-hue] input[type="range"]')) return
        this.hue = Number(event.target.value)
        this.preview({ h: this.hue })
    },

    commitHue(event) {
        if (event.target.matches('[data-color-picker-hue] input[type="range"]')) this.commit()
    },

    async pick() {
        try { await this.$wire.setColor((await new window.EyeDropper().open()).sRGBHex) } catch {}
    },

    point(area, event) {
        const box = area.getBoundingClientRect()
        this.preview({ s: clamp((event.clientX - box.left) / (box.width || 1)), v: clamp(1 - (event.clientY - box.top) / (box.height || 1)) })
    },

    start(event) {
        const area = event.target.closest('[data-slot="color-picker-area"]')
        if (!area || event.button !== 0) return
        event.preventDefault()
        area.focus()
        area.setPointerCapture(event.pointerId)
        this.pointer = event.pointerId
        this.point(area, event)
    },

    move(event) {
        const area = event.target.closest('[data-slot="color-picker-area"]')
        if (area && this.pointer === event.pointerId) this.point(area, event)
    },

    end(event) {
        if (this.pointer !== event.pointerId) return
        this.pointer = null
        this.commit()
    },

    cancel() {
        this.pointer = null
        this.draft = null
        this.paint()
    },

    key(event) {
        if (!event.target.matches('[data-slot="color-picker-area"]')) return
        const step = event.shiftKey ? .1 : .01
        const move = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] }[event.key]
        if (!move) return
        event.preventDefault()
        this.preview({ s: clamp(this.hsv.s + move[0]), v: clamp(this.hsv.v + move[1]) })
        this.commit()
    },
})
