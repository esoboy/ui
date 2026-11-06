// Native radio groups cannot clear their current choice. This is the only extra
// interaction a single toggle group needs; Livewire still owns the selected value.
export default binding => ({
    wasChecked: false,
    remember(event) {
        const input = event.target.matches('input') ? event.target : event.target.closest('[data-slot="toggle-group-item"]')?.querySelector('input')
        this.wasChecked = input?.type === 'radio' && input.checked
    },
    clear(event) {
        const input = event.target
        if (input.type !== 'radio' || !this.wasChecked || input.disabled || input.hasAttribute('data-theme-value')) return
        event.preventDefault()
        if (binding.property) this.$wire.$set(binding.property, null, binding.live)
        this.$nextTick(() => { input.checked = false })
        this.wasChecked = false
    },
})
