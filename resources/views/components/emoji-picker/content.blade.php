@blaze

{{-- Emojibase's categories, the component group (skin tones, hair) left out; the script fills them with the template's button. --}}
@php
$categories = [
    'smileys-emotion' => __('ui::emoji-picker.smileys-emotion'),
    'people-body' => __('ui::emoji-picker.people-body'),
    'animals-nature' => __('ui::emoji-picker.animals-nature'),
    'food-drink' => __('ui::emoji-picker.food-drink'),
    'travel-places' => __('ui::emoji-picker.travel-places'),
    'activities' => __('ui::emoji-picker.activities'),
    'objects' => __('ui::emoji-picker.objects'),
    'symbols' => __('ui::emoji-picker.symbols'),
    'flags' => __('ui::emoji-picker.flags'),
];
@endphp

<div {{ $attributes->merge(['data-slot' => 'emoji-picker-viewport']) }} wire:ignore>
    <div data-slot="emoji-picker-loading"><x-ui::icon name="loader" /></div>
    <div data-slot="emoji-picker-empty" hidden>{{ __('ui::emoji-picker.empty') }}</div>
    <div data-slot="emoji-picker-list" role="listbox" aria-label="{{ __('ui::emoji-picker.label') }}" hidden>
        @foreach ($categories as $category => $label)
        <div data-slot="emoji-picker-category" data-category="{{ $category }}" role="group" aria-label="{{ $label }}">
            <div data-slot="emoji-picker-category-header" aria-hidden="true">{{ $label }}</div>
            <div data-slot="emoji-picker-emojis"></div>
        </div>
        @endforeach
    </div>
    <template data-template="emoji"><button type="button" role="option" aria-selected="false" tabindex="-1" data-slot="emoji-picker-emoji"></button></template>
</div>
