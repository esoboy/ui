@blaze

@use('Esoboy\Ui\Support\EditorControls')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'format' => 'full',
])

{{-- Any control by name, when the format has it: a button, a menu or a popover. In a toolbar, priority (1 to 3, by default the toolbar's) says how soon it goes into More. --}}
@props([
    'name',
    'priority' => null,
])

@php
$rich = RichText::resolve($format);
@endphp

@if (EditorControls::allowed($name, $rich))
<div data-slot="editor-control" data-priority="{{ $priority ?? EditorControls::priority($name) }}">
    @switch($name)
    @case('heading')
    @case('font-family')
    @case('font-size')
    @case('text-align')
    @case('line-height')
    @case('callout')
    <x-ui::editor.menu :name="$name" :attributes="$attributes" />
    @break
    @case('color')
    <x-ui::editor.color :attributes="$attributes" />
    @break
    @case('spacing')
    <x-ui::editor.spacing :attributes="$attributes" />
    @break
    @case('link')
    <x-ui::editor.link :attributes="$attributes" />
    @break
    @case('video')
    <x-ui::editor.video :attributes="$attributes" />
    @break
    @case('math')
    <x-ui::editor.math :attributes="$attributes" />
    @break
    @case('emoji')
    <x-ui::editor.emoji :attributes="$attributes" />
    @break
    @default
    <x-ui::editor.button :command="$name" :attributes="$attributes" />
    @endswitch
</div>
@endif
