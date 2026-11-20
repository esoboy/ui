<div {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'class', 'invalid', 'placeholder']) }}>
    @include('ui::livewire.partials.picker-trigger', [
        'picker' => 'time-picker', 'label' => $time ?? '', 'icon' => 'clock', 'prompt' => __('ui::ui.select_time'),
    ])
    @teleport('#'.$contentTarget)
        @include('ui::livewire.partials.time-panel', ['awaitingDate' => false])
    @endteleport
    @if ($attributes->get('name') && $time !== null)
        <input type="hidden" {{ $attributes->only(['name', 'form']) }} value="{{ $time }}" @disabled($disabled)>
    @endif
</div>
