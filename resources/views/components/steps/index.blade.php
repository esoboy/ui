@props(['size' => 'default', 'items', 'current' => 1, 'orientation' => 'horizontal', 'status' => 'normal', 'emptyTitle' => null, 'emptyDescription' => null])
@php
    $current = filter_var($current, FILTER_VALIDATE_INT);
    if (!is_array($items) || !array_is_list($items) || $current === false || $current < 1 || ($items !== [] && $current > count($items) + 1)
        || !in_array($orientation, ['horizontal', 'vertical'], true) || !in_array($status, ['normal', 'error'], true)) {
        throw new \InvalidArgumentException(__('ui::errors.steps'));
    }
@endphp
<ol {{ $attributes->merge(['data-size' => $size, 'class' => 'ui-steps', 'data-orientation' => $orientation]) }}>
    @forelse ($items as $item)
        @php
            $number = $loop->iteration;
            $state = $number < $current ? 'complete' : ($number === $current ? ($status === 'error' ? 'error' : 'current') : 'upcoming');
        @endphp
        <x-ui::steps.item :number="$number" :state="$state">
            <x-ui::steps.title>{{ $item['title'] }}</x-ui::steps.title>
            @isset ($item['description'])<x-ui::steps.description>{{ $item['description'] }}</x-ui::steps.description>@endisset
        </x-ui::steps.item>
    @empty
        <li role="presentation">
            @include('ui::partials.empty-state', ['emptyMessage' => 'steps'])
        </li>
    @endforelse
</ol>
