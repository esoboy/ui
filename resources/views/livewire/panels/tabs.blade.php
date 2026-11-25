<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-tabs group/tabs')->merge(['data-orientation' => $tabsOrientation]) }} x-data="uiTabs">
    {{ $slot }}</div>
