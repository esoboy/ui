<div {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes) }} {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'class', 'invalid', 'placeholder'])->class('ui-picker-body') }}>
    @include('ui::livewire.partials.picker-trigger', [
        'picker' => 'time-picker', 'label' => $time ?? '', 'icon' => 'clock', 'prompt' => __('ui::ui.select_time'),
    ])
    @teleport('#'.$contentTarget)
        @include('ui::livewire.partials.time-panel', ['awaitingDate' => false])
    @endteleport
    @include('ui::livewire.partials.selection-input', ['selection' => $time === null ? [] : [$time]])
</div>
