<div role="radiogroup" {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->class('ui-radio-group')->merge(['data-size' => $size]) }}>{{ $slot }}</div>
