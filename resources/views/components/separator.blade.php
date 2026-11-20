@blaze
@props(['orientation' => 'horizontal'])
<div {{ $attributes->merge(['role' => 'separator', 'aria-orientation' => $orientation, 'data-orientation' => $orientation, 'data-esoboy-ui' => '', 'data-slot' => 'separator']) }}>
</div>
