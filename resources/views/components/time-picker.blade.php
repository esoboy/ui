@blaze

{{-- shadcn's time picker: a native time Input without the browser's picker icon. step="1" shows seconds. --}}
<x-ui::input type="time" :attributes="$attributes->merge(['data-time-picker' => true])" />
