@blaze
@props(['status' => null, 'variant' => 'default'])
<div {{ $attributes->class(['ui-alert group/alert', 'ui-alert-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['role' => 'alert']) }}>
    {{ $slot }}</div>
