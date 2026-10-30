@blaze

@use('Esoboy\Ui\Support\EditorControls')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'format' => 'full',
])

{{-- One line: without a slot, every control the format has, in groups. What doesn't fit goes into More, the least used first (a control's priority). --}}
@php
$rich = RichText::resolve($format);
@endphp

<div {{ $attributes->merge(['data-slot' => 'editor-toolbar', 'role' => 'toolbar', 'aria-label' => __('ui::editor.toolbar')]) }} wire:ignore>
    @if ($slot->isEmpty())
    @foreach (EditorControls::toolbar($rich) as $group)
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
    <x-ui::popover>
        <x-ui::popover.trigger variant="ghost" size="icon-sm" data-editor-more hidden :aria-label="__('ui::editor.more')"><x-ui::icon name="ellipsis" /></x-ui::popover.trigger>
        <x-ui::popover.content align="end" data-editor-more-content :aria-label="__('ui::editor.more')"></x-ui::popover.content>
    </x-ui::popover>
</div>
