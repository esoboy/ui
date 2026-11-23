@props(['size' => 50])
<div {{ $attributes->style(['--ui-panel-size: '.$size])->class('ui-resizable-panel') }}>{{ $slot }}
</div>
