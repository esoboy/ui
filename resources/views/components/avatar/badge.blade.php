@props(['status' => null])
<span {{ $attributes->class(['ui-avatar-badge', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</span>
