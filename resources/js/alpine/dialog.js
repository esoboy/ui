import { confirmation } from './confirm.js'

export default binding => confirmation({
    backdropPointer: null,
    swipe: null,
    init() {
        if (binding?.property) {
            this.$watch('$wire.' + binding.property, value => this.setOpen(value))
            this.$nextTick(() => this.setOpen(this.$wire.$get(binding.property)))
        }
    },
    setOpen(open) {
        if (!!open === this.$el.open) return
        open ? this.$el.showModal() : this.$el.close()
    },
    changed() {
        if (!this.$el.open) this.confirmClosed()
        if (binding?.property && this.$wire.$get(binding.property) !== this.$el.open) this.$wire.$set(binding.property, this.$el.open, binding.live)
    },
    requested(detail) { if (detail.name === this.$el.id) this.setOpen(detail.open) },
    backdrop(event) {
        if (event.target !== this.$el || this.$el.getAttribute('role') === 'alertdialog') return
        const box = this.$el.getBoundingClientRect()
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) this.$el.close()
    },
    start(event) {
        if (!this.$el.matches('[data-slot="drawer-popup"][open]') || event.button !== 0 || !event.isPrimary || event.target.closest('dialog') !== this.$el) return
        if (event.target === this.$el) {
            this.backdropPointer = event.pointerId
            return
        }
        if (event.target.closest('input,textarea,select,button,a[href],[contenteditable],[data-no-swipe]')) return
        for (let node = event.target; node && node !== this.$el; node = node.parentElement) if (node.scrollTop > 0 || Math.abs(node.scrollLeft) > 0) return
        const vertical = this.$el.dataset.swipeAxis !== 'x'
        this.swipe = { id: event.pointerId, vertical, sign: ['up', 'left'].includes(this.$el.dataset.swipeDirection) ? -1 : 1, start: vertical ? event.clientY : event.clientX, time: event.timeStamp, movement: 0 }
    },
    move(event) {
        if (!this.swipe || event.pointerId !== this.swipe.id) return
        const s = this.swipe
        const delta = ((s.vertical ? event.clientY : event.clientX) - s.start) * s.sign
        s.movement = delta > 0 ? delta : delta / 8
        if (Math.abs(s.movement) > 2 && !this.$el.hasAttribute('data-swiping')) {
            // Leave ordinary clicks on slot content at their original target.
            this.$el.setPointerCapture(event.pointerId)
            this.$el.setAttribute('data-swiping', '')
        }
        this.$el.style.setProperty(s.vertical ? '--drawer-swipe-movement-y' : '--drawer-swipe-movement-x', `${s.movement * s.sign}px`)
    },
    end(event) {
        if (this.backdropPointer === event.pointerId) {
            this.backdropPointer = null
            if (event.type === 'pointerup') this.backdrop(event)
            return
        }
        if (!this.swipe || event.pointerId !== this.swipe.id) return
        const s = this.swipe
        const size = s.vertical ? this.$el.offsetHeight : this.$el.offsetWidth
        this.$el.removeAttribute('data-swiping')
        if (event.type !== 'pointercancel' && (s.movement > size / 4 || s.movement > 20 && s.movement / Math.max(event.timeStamp - s.time, 1) > .5)) this.$el.close()
        this.$el.style.removeProperty('--drawer-swipe-movement-x')
        this.$el.style.removeProperty('--drawer-swipe-movement-y')
        if (this.$el.hasPointerCapture(event.pointerId)) this.$el.releasePointerCapture(event.pointerId)
        this.swipe = null
    },
})
