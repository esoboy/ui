@props(['initialValue' => null, 'orientation' => 'horizontal'])
<div {{ $attributes->class('ui-tabs group/tabs')->merge(['data-orientation' => $orientation]) }} x-data="uiTabs(@js($initialValue), @js($orientation))" x-modelable="active" x-id="['ui-tabs']">
    {{ $slot }}</div>
