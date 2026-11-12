export const timeHourChooser = part => ({
    choosingHour: false,
    jumpingHour: false,

    focusTimeChoice(selector) {
        this.$nextTick(() => {
            const panel = this.$refs.timeOptions
            if (! panel?.closest(':popover-open')) return

            panel.scrollIntoView({ block: 'nearest' })
            panel.querySelector(selector)?.focus()
        })
    },

    toggleHourChooser() {
        this.choosingHour = ! this.choosingHour
        if (this.choosingHour) this.focusTimeChoice('[data-time-hour-grid] [aria-pressed="true"]')
    },

    cancelHourChooser(event) {
        if (! this.choosingHour) return

        event.preventDefault()
        event.stopPropagation()
        this.choosingHour = false
        this.$nextTick(() => this.$refs.hourToggle.focus())
    },

    hourPopoverToggled(event) {
        if (event.newState === 'closed' && event.target.contains(this.$refs.timeOptions)) this.choosingHour = false
    },

    async jumpToHour(hour) {
        if (this.jumpingHour) return

        this.jumpingHour = true
        try {
            await this.$wire.showTimeHour(...(part === null ? [hour] : [part, hour]))
            if (! this.choosingHour) return

            this.choosingHour = false
            this.focusTimeChoice('[data-combined] [tabindex="0"]')
        } finally {
            this.jumpingHour = false
        }
    },
})

export default () => ({
    scrollToTime(event) {
        if (event.newState !== 'open' || ! event.target.matches('[data-time-picker-content], [data-date-picker-content]')) return

        this.scrollGrids(event.target)
    },

    unitClicked(event) {
        const panel = event.currentTarget
        if (event.target.closest('[role="tab"]')) this.$nextTick(() => this.scrollGrids(panel))
    },

    scrollGrids(panel) {
        for (const grid of panel.querySelectorAll('[data-slot="time-options-grid"]')) {
            const current = grid.querySelector('[aria-pressed="true"]')
            if (! grid.clientHeight || ! current) continue

            const rowHeight = current.offsetHeight + parseFloat(getComputedStyle(grid).rowGap)
            const row = Math.floor(current.offsetTop / rowHeight)
            const visibleRows = Math.floor(grid.clientHeight / rowHeight)
            grid.scrollTop = (row - Math.floor(visibleRows / 2)) * rowHeight
        }
    },

    timeKey(event) {
        const options = [...event.currentTarget.querySelectorAll('[data-time-option]')]
        const index = options.indexOf(event.target)
        if (index === -1) return

        const columns = Number(event.currentTarget.dataset.columns)
        const direction = getComputedStyle(event.currentTarget).direction === 'rtl' ? -1 : 1
        const next = {
            ArrowLeft: index - direction,
            ArrowRight: index + direction,
            ArrowUp: index - columns,
            ArrowDown: index + columns,
            Home: 0,
            End: options.length - 1,
        }[event.key]
        if (next === undefined) return

        event.preventDefault()
        const target = options[Math.max(0, Math.min(options.length - 1, next))]
        event.currentTarget.querySelector('[tabindex="0"]')?.setAttribute('tabindex', '-1')
        target.tabIndex = 0
        target.focus()
    },
})
