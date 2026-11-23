@blaze
@props(['invalid' => false, 'size' => 'default', 'multiple' => false, 'rows' => null])
<div data-size="{{ $size }}" class="ui-native-select ui-native-select-wrapper group/native-select {{ $attributes->get('class') }}">
    <select data-size="{{ $size }}" @if($multiple) multiple size="{{ $rows ?? 4 }}" @endif
        {{ $attributes->except('class')->class('ui-native-select-control')->merge(['aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $slot }}</select>
    @unless ($multiple)
        <x-ui::icon class="ui-native-select-icon" name="chevron-down" />
    @endunless
</div>
