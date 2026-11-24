@props(['size' => 50])
<div {{ $attributes->style(['--panel-size: '.$size])->class('ui-resizable-panel') }}>{{ $slot }}
</div>
