// A binding adapter, not a state store. Livewire or the native form control owns
// the value; Alpine only reads it to paint and operate the surrounding interaction.
import { emit } from './interaction.js'

const nativeModel = owner => {
    const input = owner.$refs.input ?? owner.$el.querySelector('[x-ref="input"]')
    return input && [...input.attributes].some(attribute => attribute.name.startsWith('wire:model')) ? input : null
}

export default binding => ({
    revision: 0,

    get value() {
        this.revision

        if (binding.property) return this.$wire.$get(binding.property)

        const input = this.$refs.input ?? this.$el.querySelector('[x-ref="input"]')

        if (input?.multiple) return [...input.selectedOptions].map(option => option.value)

        if (binding.json) { try { return JSON.parse(input?.value || 'null') } catch { return null } }
        return input?.value || null
    },

    set value(value) {
        const input = this.$refs.input ?? this.$el.querySelector('[x-ref="input"]')
        if (input?.multiple) {
            for (const option of input.options) option.selected = (value ?? []).map(String).includes(option.value)
        } else if (input) {
            input.value = binding.json ? JSON.stringify(value) : value ?? ''
        }
        if (binding.property) {
            // Let Livewire's native directive handle modifiers and request timing.
            // The immediate property update keeps the interaction in sync while deferred.
            this.$wire.$set(binding.property, value, !nativeModel(this) && binding.live && !binding.blur)
        } else {
            if (! input) return
            this.revision++
        }
    },

    changed() {
        const input = nativeModel(this)
        if (input) {
            const value = this.value
            emit(input, 'input', value)
            emit(input, 'change', value)
            // A native input represents null as an empty string. Keep the typed
            // Livewire value after its event handlers schedule the request.
            if (value === null && binding.property) this.$wire.$set(binding.property, null, false)
            return
        }
        this.$dispatch('input')
        this.$dispatch('change')
    },

    blur() {
        const input = nativeModel(this)
        if (input) {
            const value = this.value
            emit(input, 'blur', value)
            if (value === null && binding.property) this.$wire.$set(binding.property, null, false)
        }
        else if (binding.property && binding.live && binding.blur) this.$wire.$commit()
    },
})
