import { listen, disconnected } from './interaction.js'

const deadlines = new Map()

export default () => ({
    _timer: null,
    _observer: null,
    _visible: false,
    _due: 0,
    _request: null,
    _stopMorph: null,

    init() {
        const name = this.$el.dataset.name
        const previous = deadlines.get(name)
        this._observer = new IntersectionObserver(([entry]) => {
            this._visible = entry.isIntersecting
            this._schedule()
        })
        this._observer.observe(this.$el)
        listen(this, document, 'visibilitychange', () => this._schedule())
        this._stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
            if (el === this.$el) this._sync()
        })
        if (previous && previous !== Number(this.$el.dataset.deadline)) this._call('restore', previous)
        else this._sync()
    },

    destroy() {
        clearTimeout(this._timer)
        this._observer?.disconnect()
        this._stopMorph?.()
        disconnected(this)
    },

    start(detail) {
        if (!detail?.name || detail.name !== this.$el.dataset.name) return
        this._call('start', detail.seconds ?? null)
    },

    _call(action, ...arguments_) {
        clearTimeout(this._timer)
        const prior = this._request
        const next = Promise.resolve(prior).catch(() => {}).then(() => this.$el.isConnected && this.$wire[action](...arguments_))
        this._request = next
        next.catch(() => {}).finally(() => {
            if (this._request !== next) return
            this._request = null
            this._schedule()
        })
    },

    _sync() {
        const { name, deadline, delay, running, started } = this.$el.dataset
        if (name && Number(deadline)) deadlines.set(name, Number(deadline))
        this._due = Number(delay) ? performance.now() + Number(delay) : 0
        this.$dispatch('ui-countdown-state', { running: running === 'true', started: started === 'true' })
        this._schedule()
    },

    _schedule() {
        clearTimeout(this._timer)
        if (!this.$el.isConnected || !this._due || this._request || !this._visible || document.hidden) return

        const remaining = this._due - performance.now()
        this._timer = setTimeout(() => {
            if (remaining > 2147483647) return this._schedule()
            this._due = 0
            this._call('tick')
        }, Math.min(2147483647, Math.max(0, remaining)))
    },
})
