export default () => ({
    focus() { this.$el.querySelector('[data-slot="questionnaire-item"][data-active]')?.focus() },
    key(event) {
        if (event.isComposing || event.repeat || event.defaultPrevented) return
        const item = event.target.closest('[data-slot="questionnaire-item"]')
        if (!item) return
        const textual = event.target.matches('input:not([type="radio"]):not([type="checkbox"]),textarea,[contenteditable]')
        if (event.key === 'Enter' && (event.ctrlKey || event.metaKey || event.target.matches('input'))) { event.preventDefault(); this.$wire.next(); return }
        if (event.ctrlKey || event.metaKey || event.altKey || textual) return
        const key = this.$el.dataset.shortcuts === 'letters' ? event.key.toUpperCase() : event.key
        const choice = [...item.querySelectorAll('[data-slot="questionnaire-choice"]')].find(choice => choice.dataset.shortcut === key)?.querySelector('input:not(:disabled)')
        if (choice) { event.preventDefault(); choice.focus(); choice.click() }
        else if (['ArrowDown', 'ArrowUp'].includes(event.key) && event.target === item) {
            event.preventDefault(); (item.querySelector('input:checked') ?? item.querySelector('input:not(:disabled)'))?.focus()
        } else if (event.key === 'ArrowLeft' && !event.target.matches('input[type="radio"]')) { event.preventDefault(); this.$wire.previous() }
        else if (event.key === 'ArrowRight' && !event.target.matches('input[type="radio"]')) { event.preventDefault(); this.$wire.next() }
    },
})
