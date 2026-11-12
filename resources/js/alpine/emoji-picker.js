import { draws } from '../support/emoji.js'
import { emit } from './interaction.js'

export default () => ({
    active: null,
    previewTimer: null,
    previewed: '',
    destroy() { clearTimeout(this.previewTimer) },
    get search() { return this.$el.querySelector('[data-emoji-picker-search]') },
    get items() { return [...this.$el.querySelectorAll('[data-slot="emoji-picker-emoji"]:not([hidden])')] },
    refresh() {
        const samples = new Map()
        for (const item of this.items) if (!samples.has(Number(item.dataset.version))) samples.set(Number(item.dataset.version), item.textContent)
        const newest = [...samples.keys()].sort((a, b) => b - a).find(version => draws(samples.get(version))) ?? Infinity
        const flags = draws('🇺🇳')
        for (const item of this.items) item.hidden = Number(item.dataset.version) > newest || !flags && /^[\u{1F1E6}-\u{1F1FF}]{2}$/u.test(item.textContent)
        for (const category of this.$el.querySelectorAll('[data-slot="emoji-picker-category"]')) {
            const count = category.querySelectorAll('[data-slot="emoji-picker-emoji"]:not([hidden])').length
            category.hidden = count === 0
            category.style.setProperty('--emoji-picker-rows', Math.ceil(count / Number(this.$el.dataset.columns)))
        }
        this.activate(this.$wire.query ? this.items[0] : null)
    },
    hover(event) { const item = event.target.closest('[data-slot="emoji-picker-emoji"]'); if (item) this.activate(item) },
    activate(item) {
        this.active?.removeAttribute('data-active'); this.active?.setAttribute('aria-selected', 'false'); this.active = item ?? null
        if (item) { item.id ||= this.$id('emoji', this.items.indexOf(item)); item.setAttribute('data-active', ''); item.setAttribute('aria-selected', 'true'); this.search?.setAttribute('aria-activedescendant', item.id) }
        else this.search?.removeAttribute('aria-activedescendant')
        const footer = this.$el.querySelector('[data-slot="emoji-picker-footer"]')
        if (!footer) return
        const glyph = item?.textContent ?? ''
        clearTimeout(this.previewTimer)
        if (glyph === this.previewed) return
        this.previewTimer = setTimeout(() => {
            this.previewed = glyph
            emit(footer, 'emoji-preview', { glyph, label: item?.getAttribute('aria-label') ?? '' })
        }, 150)
    },
    close() { const popup = this.$el.parentElement.closest('[popover]'); if (popup?.matches(':popover-open')) popup.hidePopover() },
    key(event) {
        if (event.key === 'Enter' && this.active) { event.preventDefault(); this.active.click(); return }
        if (!this.active && event.target === this.search && ['ArrowLeft', 'ArrowRight'].includes(event.key)) return
        const step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -Number(this.$el.dataset.columns), ArrowDown: Number(this.$el.dataset.columns) }[event.key]
        if (!step) return
        event.preventDefault()
        this.activate(this.items[Math.max(0, Math.min(this.items.length - 1, this.items.indexOf(this.active) + step))]); this.active?.scrollIntoView({ block: 'nearest' })
    },
})
