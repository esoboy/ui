@blaze

@use('Esoboy\Ui\Support\RichText')

@aware([
    'format' => 'full',
])

{{-- Text color and highlight from the fixed palette, as the format has them; a swatch applies and closes. --}}
@php
$rich = RichText::resolve($format);
$names = [
    'gray' => __('ui::editor.color-gray'),
    'red' => __('ui::editor.color-red'),
    'orange' => __('ui::editor.color-orange'),
    'yellow' => __('ui::editor.color-yellow'),
    'green' => __('ui::editor.color-green'),
    'blue' => __('ui::editor.color-blue'),
    'purple' => __('ui::editor.color-purple'),
    'pink' => __('ui::editor.color-pink'),
];
@endphp

<x-ui::popover>
    <x-ui::popover.trigger variant="ghost" size="icon-sm" data-editor-opens="color" :aria-label="__('ui::editor.color')" :attributes="$attributes"><x-ui::icon name="baseline" /></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-color-content>
        @if ($rich->has('color'))
        <div data-slot="editor-swatches" role="group" aria-label="{{ __('ui::editor.text-color') }}">
            <p data-slot="editor-swatches-label">{{ __('ui::editor.text-color') }}</p>
            <button type="button" data-slot="editor-swatch" data-editor-command="color" data-editor-value="" aria-pressed="false" aria-label="{{ __('ui::editor.color-default') }}"><span><x-ui::icon name="baseline" /></span></button>
            @foreach (RichText::COLORS as $key => $color)
            <button type="button" data-slot="editor-swatch" data-editor-command="color" data-editor-value="{{ $color }}" aria-pressed="false" aria-label="{{ $names[$key] }}"><span style="color: {{ $color }}"><x-ui::icon name="baseline" /></span></button>
            @endforeach
        </div>
        @endif
        @if ($rich->has('highlight'))
        <div data-slot="editor-swatches" role="group" aria-label="{{ __('ui::editor.highlight') }}">
            <p data-slot="editor-swatches-label">{{ __('ui::editor.highlight') }}</p>
            <button type="button" data-slot="editor-swatch" data-editor-command="highlight" data-editor-value="" aria-pressed="false" aria-label="{{ __('ui::editor.highlight-none') }}"><x-ui::icon name="ban" /></button>
            @foreach (RichText::HIGHLIGHTS as $key => $color)
            <button type="button" data-slot="editor-swatch" data-editor-command="highlight" data-editor-value="{{ $color }}" aria-pressed="false" aria-label="{{ $names[$key] }}"><span style="background-color: {{ $color }}"></span></button>
            @endforeach
        </div>
        @endif
    </x-ui::popover.content>
</x-ui::popover>
