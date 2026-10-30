@blaze

@use('Esoboy\Ui\Support\RichText')

{{-- Not in shadcn: the contents of rich text, as shadcn's docs show "On This Page": an <x-ui::anchor> of its headings linking to them in the <x-ui::rich-text> showing the same HTML, the one being read marked. The top three levels it has, each under the one before; the slot is its title. Nothing without headings. --}}
@props([
    'html' => null,
    'format' => 'full',
])

@php
$headings = RichText::headings($html, $format);
$levels = array_values(array_unique(array_column($headings, 'level')));
sort($levels);
$levels = array_slice($levels, 0, 3);
@endphp

@if ($headings)
<x-ui::anchor :attributes="$attributes">
    @if (! $slot->isEmpty())
    <x-ui::anchor.title>{{ $slot }}</x-ui::anchor.title>
    @endif
    @foreach ($headings as $heading)
    @if (in_array($heading['level'], $levels, true))
    <x-ui::anchor.item :href="'#'.$heading['id']" :level="array_search($heading['level'], $levels, true) + 1">{{ $heading['text'] }}</x-ui::anchor.item>
    @endif
    @endforeach
</x-ui::anchor>
@endif
