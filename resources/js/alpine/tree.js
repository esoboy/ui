export default () => ({
    drag: null,
    dropped: false,
    hold: 0,
    ranges: [],
    popupOpen: false,
    restorePopup: false,
    focusedItem: null,
    stopBeforeMorph: null,
    stopAfterMorph: null,
    init() {
        if (!this.$el.querySelector('[data-slot="tree-select-content"]')) return
        this.stopBeforeMorph = window.Livewire?.hook('morph', ({ el }) => {
            if (el !== this.$el) return
            this.restorePopup = this.popupOpen
            this.focusedItem = document.activeElement?.closest('[data-slot="tree-item"]')?.dataset.value ?? null
        })
        this.stopAfterMorph = window.Livewire?.hook('morphed', ({ el }) => {
            if (el !== this.$el || !this.restorePopup) return
            const panel = this.$el.querySelector('[data-slot="tree-select-content"]')
            if (!panel.matches(':popover-open')) panel.showPopover({ source: this.$el.querySelector('[data-tree-select-trigger]') })
            if (this.focusedItem) this.focus(this.visible.find(item => item.dataset.value === this.focusedItem))
        })
    },
    get tree() { return this.$el.matches('[data-slot="tree"]') ? this.$el : this.$el.querySelector('[data-slot="tree"]') },
    get visible() { return [...this.tree.querySelectorAll('[data-slot="tree-item"]')].filter(item => item.getClientRects().length > 0) },
    focus(item) {
        if (! item) return
        for (const node of this.tree.querySelectorAll('[data-slot="tree-item"]')) node.tabIndex = node === item ? 0 : -1
        item.focus()
    },
    async key(event) {
        const trigger = this.$el.querySelector('[data-tree-select-trigger]')
        const panel = this.$el.querySelector('[data-slot="tree-select-content"]')
        if (event.target === trigger && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault(); if (! panel.matches(':popover-open')) panel.showPopover({ source: trigger }); this.focus(this.visible[0]); return
        }
        const search = this.tree.querySelector('[data-tree-search]')
        if (event.target === search && event.key === 'ArrowDown') { event.preventDefault(); this.focus(this.visible[0]); return }
        const item = event.target.closest?.('[data-slot="tree-item"]')
        if (! item) return
        const visible = this.visible, index = visible.indexOf(item)
        const next = { ArrowDown: visible[Math.min(index + 1, visible.length - 1)], ArrowUp: visible[Math.max(0, index - 1)], Home: visible[0], End: visible.at(-1) }[event.key]
        if (next) { event.preventDefault(); if (event.key === 'ArrowUp' && index === 0 && search) search.focus(); else this.focus(next); return }
        if (['Enter', ' '].includes(event.key)) { event.preventDefault(); await this.$wire.choose(item.dataset.value); return }
        if (event.key === 'ArrowRight' && item.hasAttribute('aria-expanded')) {
            event.preventDefault()
            if (item.getAttribute('aria-expanded') === 'false') await this.$wire.expand(item.dataset.value)
            else this.focus(item.querySelector('[data-slot="tree-group"] > [data-slot="tree-item"]'))
        }
        if (event.key === 'ArrowLeft') {
            event.preventDefault()
            if (item.getAttribute('aria-expanded') === 'true') await this.$wire.expand(item.dataset.value)
            else this.focus(item.parentElement.closest('[data-slot="tree-item"]'))
        }
    },
    toggled(event) {
        if (! event.target.matches('[data-slot="tree-select-content"]')) return
        this.popupOpen = event.newState === 'open'
        const trigger = this.$el.querySelector('[data-tree-select-trigger]')
        trigger?.setAttribute('aria-expanded', String(event.newState === 'open'))
        if (event.newState === 'open' && !event.target.contains(document.activeElement)) (this.tree.querySelector('[data-tree-search]') ?? this.visible[0])?.focus()
    },
    close() {
        if (this.tree.hasAttribute('data-checkable')) return
        const panel = this.$el.querySelector('[data-slot="tree-select-content"]')
        if (panel?.matches(':popover-open')) { panel.hidePopover(); this.$el.querySelector('[data-tree-select-trigger]')?.focus() }
    },
    highlight(query) {
        this.$nextTick(() => {
            if (! window.CSS?.highlights || typeof Highlight === 'undefined') return
            const ranges = []
            if (query) for (const label of this.tree.querySelectorAll('[data-slot="tree-item-label"]')) {
                const text = label.firstChild
                const index = text?.nodeType === Node.TEXT_NODE ? text.textContent.toLowerCase().indexOf(query.toLowerCase()) : -1
                if (index < 0) continue
                const range = document.createRange(); range.setStart(text, index); range.setEnd(text, index + query.length); ranges.push(range)
            }
            this.ranges = ranges
            const all = [...document.querySelectorAll('[x-data="uiTree"]')].flatMap(root => window.Alpine.$data(root).ranges ?? [])
            all.length ? CSS.highlights.set('ui-tree-match', new Highlight(...all)) : CSS.highlights.delete('ui-tree-match')
        })
    },
    press(event) {
        if (! this.$el.hasAttribute('data-draggable') || event.button !== 0) return
        const item = event.target.closest?.('[data-slot="tree-item"]')
        if (! item || item.getAttribute('aria-disabled') === 'true') return
        this.drag = { item, x: event.clientX, y: event.clientY, target: null, position: null, active: false }
        if (event.pointerType === 'touch') this.hold = setTimeout(() => this.start(), 350)
    },
    start() {
        if (! this.drag) return
        this.drag.active = true
        this.drag.item.setAttribute('data-dragging', '')
        this.$el.setAttribute('data-dragging', '')
    },
    move(event) {
        if (! this.drag) return
        if (! this.drag.active && Math.hypot(event.clientX - this.drag.x, event.clientY - this.drag.y) >= 5) {
            clearTimeout(this.hold)
            if (event.pointerType === 'touch') { this.drag = null; return }
            this.start()
        }
        if (! this.drag.active) return
        event.preventDefault()
        const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-slot="tree-item"]')
        this.drag.target?.removeAttribute('data-drop')
        if (! target || target === this.drag.item || this.drag.item.contains(target) || ! this.tree.contains(target) || target.getAttribute('aria-disabled') === 'true') { this.drag.target = null; return }
        const rect = target.querySelector('[data-slot="tree-item-row"]').getBoundingClientRect()
        const offset = (event.clientY - rect.top) / rect.height
        this.drag.target = target
        this.drag.position = offset < 0.25 ? 'before' : offset > 0.75 ? 'after' : 'inside'
        target.setAttribute('data-drop', this.drag.position)
    },
    end(drop) {
        clearTimeout(this.hold)
        if (! this.drag) return
        const drag = this.drag; this.drag = null
        drag.item.removeAttribute('data-dragging'); drag.target?.removeAttribute('data-drop'); this.$el.removeAttribute('data-dragging')
        if (! drag.active) return
        this.dropped = true; setTimeout(() => { this.dropped = false })
        if (drop && drag.target) this.$dispatch('move', { value: drag.item.dataset.value, target: drag.target.dataset.value, position: drag.position })
    },
    suppress(event) { if (this.dropped) { event.preventDefault(); event.stopImmediatePropagation(); this.dropped = false } },
    destroy() { this.stopBeforeMorph?.(); this.stopAfterMorph?.(); clearTimeout(this.hold); this.ranges = [] },
})
