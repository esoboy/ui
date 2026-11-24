@blaze
@props(['status' => null])
<li {{ $attributes->class(['ui-timeline-item', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</li>
