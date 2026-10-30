@blaze

{{-- ui.js renders a chip per chosen item from the template, in front of the input. --}}
<div {{ $attributes->merge(['data-slot' => 'combobox-chips', 'data-combobox-anchor' => true]) }}>
    <template data-combobox-chip><x-ui::combobox.chip /></template>
    {{ $slot }}
</div>
