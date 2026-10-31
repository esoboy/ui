@blaze

@use('Esoboy\Ui\Support\RichText')

{{-- A control's dropdown: a choice of values (paragraph style, font, size, alignment, line height, callout, media size, an embed's shape, cell alignment), its button showing the current one (the control's name for none: font and size), or the table's row and column commands. An item without a command labels the items after it. --}}
@props([
    'name',
])

@php
$fonts = [
    'sans-serif' => __('ui::editor.font-sans-serif'),
    'serif' => __('ui::editor.font-serif'),
    'monospace' => __('ui::editor.font-monospace'),
    'arial' => __('ui::editor.font-arial'),
    'georgia' => __('ui::editor.font-georgia'),
    'times-new-roman' => __('ui::editor.font-times-new-roman'),
    'courier-new' => __('ui::editor.font-courier-new'),
    'microsoft-yahei' => __('ui::editor.font-microsoft-yahei'),
    'simsun' => __('ui::editor.font-simsun'),
    'simhei' => __('ui::editor.font-simhei'),
    'kaiti' => __('ui::editor.font-kaiti'),
    'fangsong' => __('ui::editor.font-fangsong'),
];
$headings = [1 => __('ui::editor.heading-1'), 2 => __('ui::editor.heading-2'), 3 => __('ui::editor.heading-3'), 4 => __('ui::editor.heading-4'), 5 => __('ui::editor.heading-5'), 6 => __('ui::editor.heading-6')];

// [label, trigger: icons or the current label, items: [command, value, label, icon, style], radio]
[$label, $trigger, $items, $radio] = match ($name) {
    'heading' => [__('ui::editor.paragraph-style'), 'icons', [
        ['paragraph', '', __('ui::editor.paragraph'), 'pilcrow', null],
        ...array_map(fn (int $level) => ['heading', (string) $level, $headings[$level], "heading-{$level}", null], RichText::HEADINGS),
    ], true],
    'font-family' => [__('ui::editor.font-family'), 'label', [
        ['font-family', '', __('ui::editor.font-default'), null, null],
        ...array_map(fn (string $key, string $font) => ['font-family', $font, $fonts[$key], null, "font-family: {$font}"], array_keys(RichText::FONTS), RichText::FONTS),
    ], true],
    'font-size' => [__('ui::editor.font-size'), 'label', [
        ['font-size', '', __('ui::editor.size-default'), null, null],
        ...array_map(fn (int $size) => ['font-size', "{$size}px", (string) $size, null, null], RichText::SIZES),
    ], true],
    'text-align' => [__('ui::editor.text-align'), 'icons', [
        ['text-align', 'left', __('ui::editor.align-left'), 'text-align-start', null],
        ['text-align', 'center', __('ui::editor.align-center'), 'text-align-center', null],
        ['text-align', 'right', __('ui::editor.align-right'), 'text-align-end', null],
        ['text-align', 'justify', __('ui::editor.align-justify'), 'text-align-justify', null],
    ], true],
    'line-height' => [__('ui::editor.line-height'), 'line-height', [
        ['line-height', '', __('ui::editor.line-height-default'), null, null],
        ...array_map(fn (string $height) => ['line-height', $height, $height, null, null], RichText::LINE_HEIGHTS),
    ], true],
    'media-size' => [__('ui::editor.media-size'), 'label', [
        ['media-size', '', __('ui::editor.size-original'), null, null],
        ['media-size', '25%', __('ui::editor.size-small'), null, null],
        ['media-size', '50%', __('ui::editor.size-medium'), null, null],
        ['media-size', '75%', __('ui::editor.size-large'), null, null],
        ['media-size', '100%', __('ui::editor.size-full'), null, null],
    ], true],
    'embed-ratio' => [__('ui::editor.embed-ratio'), 'label', [
        ['embed-ratio', '16 / 9', '16:9', null, null],
        ['embed-ratio', '4 / 3', '4:3', null, null],
        ['embed-ratio', '1 / 1', '1:1', null, null],
        ['embed-ratio', '9 / 16', '9:16', null, null],
    ], true],
    'callout' => [__('ui::editor.callout'), 'message-square-warning', [
        ['callout', '', __('ui::editor.callout-none'), 'ban', null],
        ['callout', RichText::CALLOUTS['info'], __('ui::editor.callout-info'), 'info', null],
        ['callout', RichText::CALLOUTS['success'], __('ui::editor.callout-success'), 'circle-check', null],
        ['callout', RichText::CALLOUTS['warning'], __('ui::editor.callout-warning'), 'triangle-alert', null],
        ['callout', RichText::CALLOUTS['danger'], __('ui::editor.callout-danger'), 'octagon-alert', null],
    ], true],
    'cell-align' => [__('ui::editor.cell-align'), 'align-vertical-justify-start', [
        ['cell-align', 'top', __('ui::editor.align-top'), 'align-vertical-justify-start', null],
        ['cell-align', 'middle', __('ui::editor.align-middle'), 'align-vertical-justify-center', null],
        ['cell-align', 'bottom', __('ui::editor.align-bottom'), 'align-vertical-justify-end', null],
    ], true],
    'table-rows' => [__('ui::editor.rows'), 'rows-3', [
        ['add-row-before', '', __('ui::editor.add-row-before'), 'between-horizontal-start', null],
        ['add-row-after', '', __('ui::editor.add-row-after'), 'between-horizontal-end', null],
        ['header-row', '', __('ui::editor.header-row'), 'panel-top', null],
        ['delete-row', '', __('ui::editor.delete-row'), 'trash', null],
    ], false],
    'table-columns' => [__('ui::editor.columns'), 'columns-3', [
        ['add-column-before', '', __('ui::editor.add-column-before'), 'between-vertical-start', null],
        ['add-column-after', '', __('ui::editor.add-column-after'), 'between-vertical-end', null],
        ['header-column', '', __('ui::editor.header-column'), 'panel-left', null],
        ['delete-column', '', __('ui::editor.delete-column'), 'trash', null],
    ], false],
    default => throw new InvalidArgumentException("[{$name}] is not an Editor menu; use heading, font-family, font-size, text-align, line-height, callout, media-size, embed-ratio, cell-align, table-rows or table-columns."),
};

// What a label button shows for no value (or a shape of its own): the font, size and
// shape buttons their name, kept short.
$none = match ($name) {
    'font-family', 'embed-ratio' => $label,
    'font-size' => __('ui::editor.size'),
    default => $items[0][2],
};
@endphp

<x-ui::dropdown-menu>
    <x-ui::dropdown-menu.trigger variant="ghost" :size="$trigger === 'label' ? 'sm' : 'icon-sm'" data-editor-menu="{{ $name }}" :aria-label="$label" :attributes="$attributes">
        @if ($trigger === 'icons')
        @foreach ($items as [, $value, , $icon])
        <x-ui::icon :name="$icon" data-editor-icon="{{ $value }}" :hidden="! $loop->first" />
        @endforeach
        @elseif ($trigger === 'label')
        <span data-editor-current data-editor-default="{{ $none }}">{{ $none }}</span>
        @else
        <x-ui::icon :name="$trigger" />
        @endif
        <x-ui::icon name="chevron-down" data-editor-chevron />
    </x-ui::dropdown-menu.trigger>
    <x-ui::dropdown-menu.content data-editor-menu-content="{{ $name }}">
        @foreach ($items as [$command, $value, $itemLabel, $icon, $style])
        @if ($command === null)
        @if (! $loop->first)
        <x-ui::dropdown-menu.separator />
        @endif
        <x-ui::dropdown-menu.label>{{ $itemLabel }}</x-ui::dropdown-menu.label>
        @continue
        @endif
        <x-ui::dropdown-menu.item :role="$radio ? 'menuitemradio' : 'menuitem'" :aria-checked="$radio ? 'false' : false" data-editor-command="{{ $command }}" data-editor-value="{{ $value }}" data-editor-label="{{ $itemLabel }}" :variant="str_starts_with($command, 'delete') ? 'destructive' : 'default'">
            @if ($icon)
            <x-ui::icon :name="$icon" />
            @endif
            <span @if ($style) style="{{ $style }}" @endif>{{ $itemLabel }}</span>
            @if ($radio)
            <span data-slot="dropdown-menu-item-indicator"><x-ui::icon name="check" /></span>
            @endif
        </x-ui::dropdown-menu.item>
        @endforeach
    </x-ui::dropdown-menu.content>
</x-ui::dropdown-menu>
