<div data-slot="time-options-grid" data-columns="{{ $unit === 'combined' ? (count($choices) <= 6 ? 2 : 3) : 6 }}" role="group" aria-label="{{ $label }}" x-on:keydown="timeKey($event)"
    @if ($unit === 'combined') data-combined wire:key="time-grid-{{ $part ?? 'value' }}-{{ $slotsForHour['hour'] }}" @endif>
    @foreach ($choices as $option => $text)
        <button type="button" wire:key="time-{{ $part ?? 'value' }}-{{ $unit }}-{{ $option }}" data-time-option="{{ $unit === 'combined' ? $text : $option }}"
            tabindex="{{ $option === $current || (! isset($choices[$current]) && $loop->first) ? 0 : -1 }}"
            aria-pressed="{{ $selected && $option === $current ? 'true' : 'false' }}"
            wire:click="pickTime({{ $partArguments }}'{{ $unit }}', {{ $option }})">{{ $text }}</button>
    @endforeach
</div>
