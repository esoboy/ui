// The server renders dates, selection and constraints. Only keyboard focus needs
// the browser; moving beyond the rendered months asks Livewire for that month.
export default () => ({
    async key(event) {
        const key = event.target.dataset?.day
        if (! key) return
        const date = new Date(`${key}T12:00:00`)
        const weekStart = Number(this.$el.dataset.weekStartsOn ?? 0)
        const weekday = (date.getDay() - weekStart + 7) % 7
        const offset = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7, Home: -weekday, End: 6 - weekday }[event.key]
        if (offset === undefined && ! ['PageUp', 'PageDown'].includes(event.key)) return
        event.preventDefault()
        if (offset !== undefined) date.setDate(date.getDate() + offset)
        else date.setMonth(date.getMonth() + (event.key === 'PageUp' ? -1 : 1))
        const target = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
        let button = this.$el.querySelector(`button[data-day="${target}"]`)
        if (! button) {
            await this.$wire.goTo(target.slice(0, 7))
            button = this.$el.querySelector(`button[data-day="${target}"]`)
        }
        button?.focus()
    },
})
