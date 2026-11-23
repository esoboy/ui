<div {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'class', 'invalid', 'placeholder'])->class('ui-picker-body') }}
    @unless ($withTime)
        x-on:value-changed.self="if (!$wire.multiple && (!$wire.range || $event.detail.value.length === 2)) close(true)"
    @endunless>
    @include('ui::livewire.partials.picker-trigger', [
        'label' => $withTime ? str_replace('T', ' ', $this->current ?? '') : implode(__('ui::ui.list_separator'), $this->selected),
        'icon' => 'calendar-days',
        'prompt' => __('ui::ui.'.($withTime ? 'select_datetime' : 'select_date')),
    ])
    @teleport('#'.$contentTarget)
        <div>
            @include('ui::livewire.partials.calendar')
            @if ($withTime)
                @include('ui::livewire.partials.time-panel', ['awaitingDate' => $this->selected === []])
            @endif
        </div>
    @endteleport
    @include('ui::livewire.partials.selection-input', [
        'selection' => $withTime && $this->current ? [$this->current] : $this->selected,
    ])
</div>
