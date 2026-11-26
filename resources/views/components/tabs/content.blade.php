<div role="tabpanel" {{ $attributes->merge(['class' => 'ui-tabs-content', 'tabindex' => 0, 'id' => $tabsScope->idFor('panel', $value), 'data-value' => $value]) }}
    aria-labelledby="{{ $tabsScope->idFor('tab', $value) }}" @if($tabsScope->selection() !== $value) hidden @endif
    wire:bind:hidden="active !== @js($value)"
>{{ $slot }}</div>
