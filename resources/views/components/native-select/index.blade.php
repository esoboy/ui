@blaze
@props(['invalid' => false, 'size' => null, 'multiple' => false, 'rows' => null])
@php($size ??= \Esoboy\Ui\View\ButtonGroup::defaultSize())
<div {{ $attributes->only('wire:key') }} data-size="{{ $size }}" class="ui-native-select ui-native-select-wrapper group/native-select {{ $attributes->get('class') }}">
    <select data-size="{{ $size }}" @if($multiple) multiple size="{{ $rows ?? 4 }}" @endif
        {{ $attributes->except(['class', 'wire:key'])->class('ui-native-select-control')->merge(['aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $slot }}</select>
    @unless ($multiple)
        <x-ui::icon class="ui-native-select-icon" name="chevron-down" />
    @endunless
</div>
