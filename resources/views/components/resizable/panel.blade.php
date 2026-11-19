@props(['size' => 50])
<div {{ $attributes->style(['--ui-panel-size: '.$size])->merge(['data-slot' => 'resizable-panel']) }}>{{ $slot }}
</div>
