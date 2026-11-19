@props(['number', 'state'])
<li {{ $attributes->merge(['data-slot' => 'steps-item', 'data-state' => $state, 'aria-current' => in_array($state, ['current', 'error'], true) ? 'step' : null]) }}>
    <span data-slot="steps-indicator" aria-hidden="true">
        @if ($state === 'complete')<x-ui::icon name="check" size="sm" />
        @elseif ($state === 'error')<x-ui::icon name="x" size="sm" />
        @else{{ $number }}@endif
    </span>
    <div data-slot="steps-label">
        <span class="ui-visually-hidden">{{ __('ui::ui.step_'.$state) }}</span>
        {{ $slot }}
    </div>
</li>
