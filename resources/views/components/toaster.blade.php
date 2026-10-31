@blaze

{{-- Place once in the layout; Ui::toast(...) and $dispatch('ui-toast', ...) show toasts here. --}}
@props([
    'limit' => 3,
    'timeout' => 5000,
])

{{-- Kept across wire:navigate, so toasts shown before a page change stay, as sonner's do in a layout. --}}
@persist('ui-toaster')
<div {{ $attributes->merge([
    'role' => 'region',
    'aria-live' => 'polite',
    'aria-atomic' => 'false',
    'aria-relevant' => 'additions text',
    'aria-label' => __('ui::toast.label'),
    'tabindex' => '-1',
    'popover' => 'manual',
    'data-slot' => 'toast-viewport',
    'data-limit' => $limit,
    'data-timeout' => $timeout,
]) }} wire:ignore>
    {{-- A toast, cloned for each one shown; the script fills it in and drops the parts it doesn't use. --}}
    <template data-template="toast">
        <div data-slot="toast" aria-modal="false" tabindex="0">
            <div data-slot="toast-content">
                <span data-slot="toast-icon"></span>
                <div>
                    <h2 data-slot="toast-title"></h2>
                    <p data-slot="toast-description"></p>
                </div>
                <x-ui::button variant="outline" size="sm" data-toast-action />
                <x-ui::button variant="ghost" size="icon-sm" data-toast-close :aria-label="__('ui::toast.close')"><x-ui::icon name="x" /></x-ui::button>
            </div>
        </div>
    </template>
    {{-- The icon of each type. --}}
    <template data-template="icon-success"><x-ui::icon name="circle-check" /></template>
    <template data-template="icon-info"><x-ui::icon name="info" /></template>
    <template data-template="icon-warning"><x-ui::icon name="triangle-alert" /></template>
    <template data-template="icon-error"><x-ui::icon name="octagon-x" /></template>
    <template data-template="icon-loading"><x-ui::icon name="loader-circle" data-spin /></template>
</div>
@endpersist
