@blaze

@use('Esoboy\Ui\Support\RichText')

{{-- Space before and after the paragraph and between its letters: a row of values each, the current one pressed. --}}
@php
$rows = [
    'margin-top' => [__('ui::editor.space-before'), RichText::SPACINGS],
    'margin-bottom' => [__('ui::editor.space-after'), RichText::SPACINGS],
    'letter-spacing' => [__('ui::editor.letter-spacing'), RichText::LETTER_SPACINGS],
];
@endphp

<x-ui::popover>
    <x-ui::popover.trigger variant="ghost" size="icon-sm" data-editor-opens="spacing" :aria-label="__('ui::editor.spacing')" :attributes="$attributes"><x-ui::icon name="unfold-vertical" /></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-spacing-content>
        @foreach ($rows as $property => [$rowLabel, $values])
        <div data-slot="editor-spacing" role="group" aria-label="{{ $rowLabel }}">
            <p data-slot="editor-swatches-label">{{ $rowLabel }}</p>
            @foreach ($values as $value)
            <button type="button" data-slot="editor-spacing-value" data-editor-command="{{ $property }}" data-editor-value="{{ $value === '0' ? '' : $value }}" aria-pressed="false" aria-label="{{ $value === '0' ? __('ui::editor.spacing-none') : $value }}">{{ $value === '0' ? __('ui::editor.spacing-none') : $value }}</button>
            @endforeach
        </div>
        @endforeach
    </x-ui::popover.content>
</x-ui::popover>
