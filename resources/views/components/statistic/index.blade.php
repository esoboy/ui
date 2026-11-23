@props(['title', 'value' => null, 'precision' => null, 'groupSeparator' => ',', 'decimalSeparator' => '.', 'prefix' => null, 'suffix' => null, 'description' => null, 'loading' => false, 'formatter' => null])
<div {{ $attributes->merge(['class' => 'ui-statistic', 'aria-busy' => $loading ? 'true' : null]) }}>
    <x-ui::statistic.label>{{ $title }}</x-ui::statistic.label>
    @if ($loading)
        <x-ui::skeleton role="status" :aria-label="__('ui::ui.loading')" />
    @else
        <x-ui::statistic.value>
            @if ($prefix !== null)<span class="ui-statistic-prefix">{{ $prefix }}</span>@endif
            <span>{{ $formatter === null ? \Esoboy\Ui\Support\StatisticValue::format($value, $precision, $groupSeparator, $decimalSeparator) : $formatter($value) }}</span>
            @if ($suffix !== null)<span class="ui-statistic-suffix">{{ $suffix }}</span>@endif
        </x-ui::statistic.value>
    @endif
    @if ($description !== null)<x-ui::statistic.description>{{ $description }}</x-ui::statistic.description>@endif
</div>
