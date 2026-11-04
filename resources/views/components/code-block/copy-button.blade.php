@blaze

@props([
    'value' => null,
    'variant' => 'ghost',
    'size' => 'icon-xs',
])

{{-- Copies value, or the code of its block. The check shows for two seconds. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge([
    'data-code-block-copy' => true,
    'data-value' => $value ?? false,
    'aria-label' => __('ui::code-block.copy'),
    'data-label-copy' => __('ui::code-block.copy'),
    'data-label-copied' => __('ui::code-block.copied'),
])">
    <x-ui::icon name="copy" data-copy-icon="copy" />
    <x-ui::icon name="check" data-copy-icon="copied" />
</x-ui::button>
<span aria-live="polite" class="sr-only" data-code-block-status></span>
