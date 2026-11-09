// Livewire remains responsible for the action. This adapter only waits for the
// confirmation UI already rendered by Blade.
export function confirmation(provider) {
    let pending = null, confirmed = false, source = null
    return Object.assign(provider, {
        ask(action, instead, element) {
            if (pending) pending(false)
            source = element; confirmed = false
            pending = result => (result ? action() : instead())
            if (this.$el.matches('dialog')) { this.$el.returnValue = ''; this.$el.showModal() }
            else this.$el.showPopover({ source: element })
        },
        confirmClick(event) { if (event.target.closest('[value="confirm"]')) confirmed = true },
        confirmClosed() {
            if (!pending) return
            const done = pending; pending = null
            done(this.$el.matches('dialog') ? this.$el.returnValue === 'confirm' : confirmed)
            source?.focus(); source = null
        },
    })
}

export function register(Livewire, Alpine) {
    let request = Promise.resolve()
    let sequence = 0

    Livewire.hook('directive.init', ({ el, directive }) => {
        if (directive.value !== 'confirm' || directive.modifiers.includes('prompt')) return
        const id = 'ui-confirm' + (directive.modifiers.includes('popover') ? '-popover' : '') + (directive.modifiers.includes('destructive') ? '-destructive' : '')
        const [question = '', label = '', description = ''] = directive.expression.replaceAll('\\n', '\n').split('|')
        el.__livewire_confirm = (action, instead) => {
            const dialog = document.getElementById(id)
            const host = dialog?.closest('[data-ui-confirm-host]')
            if (!host) return window.confirm(question || 'Are you sure?') ? action() : instead()

            const current = ++sequence
            request = request.catch(() => {}).then(async () => {
                if (current !== sequence || !el.isConnected) return instead()
                await Livewire.find(host.getAttribute('wire:id')).prepare(id, question, label, description)
                if (current !== sequence || !el.isConnected || !host.isConnected) return instead()
                Alpine.evaluate(document.getElementById(id), 'ask(action, instead, element)', { scope: { action, instead, element: el } })
            }).catch(() => instead())
        }
    })
}
