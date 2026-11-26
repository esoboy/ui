@include('ui::partials.number-input')

@script
<script>
    $wire.$js('proposeNumber', (input) => {
        const value = input.value
        const badInput = input.validity.badInput
        input.value = $wire.externalValue ?? ''
        if (!badInput) $wire.propose(value)
    })
</script>
@endscript
