@blaze

@props([
    'multiple' => false,
    'variant' => 'default',
    'size' => 'default',
    'spacing' => 2,
    'orientation' => 'horizontal',
    'value' => null,
    'uiModel' => null,
    'uiValue' => null,
    'uiName' => null,
])

<div {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge([
    'role' => 'group', ...(!$multiple ? ['x-data' => 'uiToggleGroup('.json_encode(\Esoboy\Ui\Support\Model::binding($attributes)).')', 'x-on:pointerdown' => 'remember($event)', 'x-on:keydown.space' => 'remember($event)', 'x-on:click' => 'clear($event)'] : []),
    'data-slot' => 'toggle-group',
    'data-variant' => $variant,
    'data-size' => $size,
    'data-spacing' => $spacing,
    'data-orientation' => $orientation,
    'data-deselectable' => ! $multiple,
    'style' => '--gap: '.$spacing.';',
]) }}>{{ $slot }}</div>
