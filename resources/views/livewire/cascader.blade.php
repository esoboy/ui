<eso-cascader {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'class', 'style'])->merge(['data-slot' => 'cascader', 'x-data' => 'uiCascader', 'x-on:keydown' => 'key($event)', 'x-on:toggle.capture' => 'toggled($event)', 'x-on:ui-cascader-picked' => 'picked($event.detail)']) }}>
    <x-ui::select.trigger :size="$settings['size'] ?? 'default'" data-cascader-trigger :data-placeholder="! $labels" :disabled="$settings['disabled'] ?? false" :commandfor="$this->getId().'-cascader-popup'" :attributes="$attributes->only(['class', 'style'])->merge($settings['validation'] ?? [])">
        <x-ui::select.value :placeholder="$labels ? implode(' '.($settings['separator'] ?? '/').' ', $labels) : ($settings['placeholder'] ?? null)" />
    </x-ui::select.trigger>
    <div id="{{ $this->getId() }}-cascader-popup" popover="auto" data-slot="cascader-content" data-side="bottom" data-align="start" style="--side-offset: 4px;" wire:ignore.self>
        @foreach ($levels as $depth => $options)
        <div data-slot="cascader-column" role="listbox" wire:key="column-{{ $depth }}">
            @foreach ($options as $option)
            @php($parent = ! empty($option['children']))
            <x-ui::select.item :value="$option['value']" data-cascader-item :data-depth="$depth" :disabled="! empty($option['disabled'])" :aria-selected="($value[$depth] ?? null) === $option['value'] && count($value) === $depth + 1 ? 'true' : 'false'" :data-expanded="$parent && ($path[$depth] ?? null) === $option['value']" :aria-haspopup="$parent ? 'listbox' : null" :wire:click="'choose('.$depth.', '.\Illuminate\Support\Js::from($option['value']).')'" wire:key="item-{{ $depth }}-{{ $option['value'] }}">
                {{ isset($settings['optionView']) ? view($settings['optionView'], ['option' => $option, 'depth' => $depth]) : $option['label'] }}
                @if ($parent)<x-slot:indicator data-parent><x-ui::icon name="chevron-right" /></x-slot:indicator>@endif
            </x-ui::select.item>
            @endforeach
        </div>
        @endforeach
    </div>
    @if ($slots->has('default')){{ $slots }}@endif
    @if ($attributes->has('name'))
        @foreach ($labels ? $value : [] as $part)
        <input type="hidden" name="{{ $attributes->get('name') }}[]" value="{{ $part }}">
        @endforeach
    @endif
</eso-cascader>
