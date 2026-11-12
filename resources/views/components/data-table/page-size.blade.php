@blaze

@props(['options'])

<div {{ $attributes->merge(['data-slot' => 'data-table-page-size']) }}>
    <span>{{ __('ui::data-table.rows_per_page') }}</span>
    <x-ui::select wire:model.live.number="perPage">
        <x-ui::select.trigger class="w-[70px]" :aria-label="__('ui::data-table.rows_per_page')">
            <x-ui::select.value />
        </x-ui::select.trigger>
        <x-ui::select.content>
            <x-ui::select.group>
                @foreach ($options as $size)
                    <x-ui::select.item :value="(string) $size">{{ $size }}</x-ui::select.item>
                @endforeach
            </x-ui::select.group>
        </x-ui::select.content>
    </x-ui::select>
</div>
