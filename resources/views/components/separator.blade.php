@blaze
@props(['orientation' => 'horizontal'])
<div {{ $attributes->merge(['role' => 'separator', 'aria-orientation' => $orientation, 'data-orientation' => $orientation, 'class' => 'ui-separator']) }}>
</div>
