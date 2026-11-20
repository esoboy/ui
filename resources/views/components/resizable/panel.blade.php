@props(['size' => 50])
<div {{ $attributes->style(['--ui-panel-size: '.$size])->merge(['data-esoboy-ui' => '', 'data-slot' => 'resizable-panel']) }}>{{ $slot }}
</div>
