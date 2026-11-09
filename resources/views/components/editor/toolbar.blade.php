@blaze

@use('Esoboy\Ui\Support\EditorControls')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'format' => 'full',
    'collapseToolbar' => true,
    'mediaPicker' => [],
])

@props([
    'collapse' => null,
])

{{-- Without a slot, every control the format has, in groups. Collapsed: one line, what doesn't fit goes into More, the least used first (a control's priority). Otherwise, all controls wrap. --}}
@php
$rich = RichText::resolve($format);
$collapse ??= $collapseToolbar;
$groups = EditorControls::toolbar($rich, $mediaPicker);
$groupCount = count($groups);
if ($collapse && ! $slot->isEmpty()) {
    $markup = \Esoboy\Ui\Support\HtmlFragment::parse((string) $slot);
    $groupCount = 1 + $markup->querySelectorAll('body > [data-slot="separator"]')->length;
}
$overflowGroups = array_keys(array_fill(0, $groupCount, true));
@endphp

<div {{ $attributes->merge(['data-slot' => 'editor-toolbar', 'role' => 'toolbar', 'aria-label' => __('ui::editor.toolbar')]) }}>
    @if ($slot->isEmpty())
    @foreach ($groups as $group)
    @if (! $loop->first)
    <x-ui::editor.separator />
    @endif
    @foreach ($group as $control => $priority)
    <x-ui::editor.control :name="$control" :priority="$priority" />
    @endforeach
    @endforeach
    @else
    {{ $slot }}
    @endif
    @if ($collapse)
    <x-ui::popover>
        <x-ui::popover.trigger><x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-more hidden :aria-label="__('ui::editor.more')"><x-ui::icon name="ellipsis" /></x-ui::editor.tool></x-ui::popover.trigger>
        <x-ui::popover.content align="end" data-editor-more-content :aria-label="__('ui::editor.more')">
            @foreach ($overflowGroups as $group)
                <div role="group" data-slot="editor-more-group" data-editor-overflow-group="{{ $group }}" hidden></div>
            @endforeach
        </x-ui::popover.content>
    </x-ui::popover>
    @endif
</div>
