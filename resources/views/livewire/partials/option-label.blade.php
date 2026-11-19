@isset($option['icon'])<x-ui::icon :name="$option['icon']" />@endisset
@if(isset($option['description']))
    <x-ui::item size="xs" class="ui-option-label" :data-compact="($optionLabelCompact ?? true) ? 'true' : 'false'">
        <x-ui::item.content>
            <x-ui::item.title>{{ $option['label'] }}</x-ui::item.title>
            <x-ui::item.description>@foreach((array) $option['description'] as $fragment)@if(!$loop->first)<!---->@endif{{ $fragment }}@endforeach</x-ui::item.description>
        </x-ui::item.content>
    </x-ui::item>
@else<span>{{ $option['label'] }}</span>@endif
