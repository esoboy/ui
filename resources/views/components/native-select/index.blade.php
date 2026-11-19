@blaze
@props(['invalid' => false, 'size' => 'default', 'multiple' => false, 'rows' => null])
<div data-slot="native-select-wrapper" data-size="{{ $size }}" class="group/native-select {{ $attributes->get('class') }}">
    <select data-size="{{ $size }}" @if($multiple) multiple size="{{ $rows ?? 4 }}" @endif
        {{ $attributes->except('class')->class('ui-native-select')->merge(['data-slot' => 'native-select', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $slot }}</select>
    @unless ($multiple)
        <x-ui::icon name="chevron-down" data-slot="native-select-icon" />
    @endunless
</div>
