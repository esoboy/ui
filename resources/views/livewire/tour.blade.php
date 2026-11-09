@php
    $titleId = 'tour-'.$this->getId().'-title';
    $descriptionId = 'tour-'.$this->getId().'-description-';
    $anchor = '--ui-tour-'.$this->getId();
@endphp
<div class="contents" x-data="uiTour" data-step="{{ $current === null ? '' : $step }}" data-tour-anchor="{{ $anchor }}"
    x-on:ui-tour.window="requested($event.detail)" x-on:keydown.escape.document="escape($event)"
    x-on:livewire:navigating.document="hide()" x-on:livewire:navigated.document="refresh()">
    <div popover="manual" data-slot="tour-spotlight" aria-hidden="true" style="position-anchor: {{ $anchor }}" wire:ignore.self @if ($blocking) data-blocking @endif></div>
    <div popover="manual" role="dialog" aria-modal="false" data-slot="tour-content" style="position-anchor: {{ $anchor }}"
        @if ($current)
            data-target="{{ $current['target'] }}" data-step-side="{{ $current['side'] }}" data-step-align="{{ $current['align'] }}"
            @if ($current['title'] !== '') aria-labelledby="{{ $titleId }}" @endif
            @if ($slots->has('step-'.$step) || trim($current['description']) !== '') aria-describedby="{{ $descriptionId.$step }}" @endif
        @endif>
        @if ($current)
            <x-ui::tour.action action="close">
                <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::common.close')"><x-ui::icon name="x" /></x-ui::button>
            </x-ui::tour.action>
        @endif
        <div data-slot="tour-body">
            @if ($current && $current['title'] !== '')
                <h2 data-slot="tour-title" id="{{ $titleId }}">{{ $current['title'] }}</h2>
            @endif
            @foreach ($steps as $index => $item)
                <div data-slot="tour-description" id="{{ $descriptionId.$index }}" @if ($step !== $index) hidden @endif wire:key="tour-step-{{ $index }}">
                    @if ($slots->has('step-'.$index)){{ $slots('step-'.$index) }}@else{!! $item['description'] !!}@endif
                </div>
            @endforeach
        </div>
        @if ($current)
            <div data-slot="tour-footer">
                <span data-slot="tour-counter">{{ __('ui::tour.counter', ['current' => $step + 1, 'total' => count($steps)]) }}</span>
                <div data-slot="tour-actions">
                    @if ($actionsView)
                        @include($actionsView, ['current' => $current, 'step' => $step, 'total' => count($steps)])
                    @else
                        @if ($step > 0)
                            <x-ui::tour.action action="previous">
                                <x-ui::button variant="outline" size="sm">{{ __('ui::tour.previous') }}</x-ui::button>
                            </x-ui::tour.action>
                        @endif
                        @if ($step < count($steps) - 1)
                            <x-ui::tour.action action="next">
                                <x-ui::button size="sm">{{ __('ui::tour.next') }}</x-ui::button>
                            </x-ui::tour.action>
                        @else
                            <x-ui::tour.action action="finish">
                                <x-ui::button size="sm">{{ __('ui::tour.finish') }}</x-ui::button>
                            </x-ui::tour.action>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
