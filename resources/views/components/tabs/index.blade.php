@props(['initialValue' => null, 'orientation' => 'horizontal'])
<div {{ $attributes->class('group/tabs')->merge(['data-esoboy-ui' => '', 'data-slot' => 'tabs', 'data-orientation' => $orientation]) }} x-data="uiTabs(@js($initialValue), @js($orientation))" x-modelable="active" x-id="['ui-tabs']">
    {{ $slot }}</div>
