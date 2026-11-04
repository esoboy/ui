@blaze

@props([
    'target' => null,
    'threshold' => 400,
])

{{-- A Float Button that shows once the window (or the target, a selector) has scrolled past threshold pixels, and scrolls back to the top. wire:ignore.self keeps morphs from hiding it again. --}}
<eso-back-to-top data-slot="back-to-top" @if ($target) data-target="{{ $target }}" @endif data-threshold="{{ $threshold }}" hidden wire:ignore.self>
    <x-ui::float-button :attributes="$attributes->merge(['aria-label' => __('ui::back-to-top.label')])"><x-ui::icon name="arrow-up" /></x-ui::float-button>
</eso-back-to-top>
<x-ui::extras />
