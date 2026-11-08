@blaze

@aware([
    'uiOpen' => false,
    'uiModel' => null,
])

@php($open = (bool) \Esoboy\Ui\Support\Model::inherited($uiModel, $uiOpen))

{{-- Any element marked data-collapsible-trigger toggles too, e.g. a Button or a sidebar menu button. --}}
<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'collapsible-trigger', 'aria-expanded' => $open ? 'true' : 'false', 'data-panel-open' => (bool) $open, 'x-bind:aria-expanded' => 'String(open)', 'x-bind:data-panel-open' => 'open']) }}>{{ $slot }}</button>
