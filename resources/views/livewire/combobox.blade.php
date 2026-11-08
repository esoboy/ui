@php
    $settings += ['multiple' => false, 'autocomplete' => false, 'filter' => true, 'button' => false, 'disabled' => false, 'placeholder' => null, 'trigger' => true, 'clearable' => false, 'empty' => null];
@endphp
<eso-combobox {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'class', 'style'])->merge(['data-slot' => 'combobox', 'data-multiple' => (bool) $settings['multiple'], 'data-autocomplete' => (bool) $settings['autocomplete'], 'x-data' => 'uiCombobox', 'x-on:click' => 'click($event)', 'x-on:input' => 'open()', 'x-on:keydown' => 'key($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:pointerdown.outside' => 'close()', 'x-on:ui-combobox-picked' => 'picked($event.detail)', 'x-effect' => '$wire.query; $wire.value; $nextTick(() => refresh())']) }}>
    @if ($settings['multiple'])
    <div data-slot="combobox-chips" data-combobox-anchor {{ $attributes->only(['class', 'style']) }}>
        @foreach ((array) $value as $chosen)
        <div data-slot="combobox-chip" data-value="{{ $chosen }}" wire:key="chip-{{ $chosen }}">
            @php($selectedLabel = $labels[(string) $chosen] ?? (string) $chosen)
            <span data-combobox-chip-label>{{ isset($settings['selectedView']) ? view($settings['selectedView'], ['value' => $chosen, 'label' => $selectedLabel]) : $selectedLabel }}</span>
            <x-ui::button variant="ghost" size="icon-xs" data-combobox-chip-remove :aria-label="__('ui::common.remove')" :wire:click="'remove('.\Illuminate\Support\Js::from($chosen).')'"><x-ui::icon name="x" /></x-ui::button>
        </div>
        @endforeach
        <x-ui::combobox.chips-input wire:model.live.debounce.150ms="query" :placeholder="$settings['placeholder']" :disabled="$settings['disabled']" :attributes="new \Illuminate\View\ComponentAttributeBag($settings['validation'] ?? [])" />
    </div>
    @elseif ($settings['button'])
        <x-ui::combobox.trigger variant="outline" :disabled="$settings['disabled']" :attributes="$attributes->only(['class', 'style'])">
            <x-ui::combobox.value :placeholder="$label ?: $settings['placeholder']" :label="$value !== null && isset($settings['selectedView']) ? view($settings['selectedView'], ['value' => $value, 'label' => $label]) : null" />
        </x-ui::combobox.trigger>
    @else
        <x-ui::combobox.input wire:model.live.debounce.150ms="query" :value="$query" :placeholder="$settings['placeholder']" :show-trigger="$settings['trigger']" :show-clear="$settings['clearable']" :disabled="$settings['disabled']" :attributes="$attributes->only(['class', 'style'])->merge($settings['validation'] ?? [])" />
    @endif
    <x-ui::combobox.content :data-empty="$choices === []" wire:key="combobox-content">
        @if ($settings['button'])<x-ui::combobox.input wire:model.live.debounce.150ms="query" :show-trigger="false" :placeholder="$settings['placeholder']" />@endif
        @if ($slots->has('empty') || $settings['empty'] !== null)
            <x-ui::combobox.empty>{{ $slots->has('empty') ? $slots('empty') : $settings['empty'] }}</x-ui::combobox.empty>
        @endif
        <x-ui::combobox.list>
            @foreach ($choices as $option)
            @php($selected = in_array($option['value'], (array) $value, true))
            <x-ui::combobox.item :value="$option['value']" :aria-selected="$selected ? 'true' : 'false'" :disabled="! empty($option['disabled'])" :wire:click="'choose('.\Illuminate\Support\Js::from($option['value']).')'">
                {{ isset($settings['optionView']) ? view($settings['optionView'], ['option' => $option, 'selected' => $selected]) : $option['label'] }}
            </x-ui::combobox.item>
            @endforeach
        </x-ui::combobox.list>
        @if ($slots->has('default'))<div data-slot="combobox-footer">{{ $slots }}</div>@endif
    </x-ui::combobox.content>
    @if ($attributes->has('name'))
        @foreach ((array) $value as $part)
        <input type="hidden" name="{{ $attributes->get('name') }}{{ $settings['multiple'] ? '[]' : '' }}" value="{{ $part }}">
        @endforeach
    @endif
</eso-combobox>
