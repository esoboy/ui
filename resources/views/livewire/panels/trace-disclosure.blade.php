<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-trace-collapsible') }} data-ui-disclosure x-data="uiDisclosure" x-id="['ui-trace']">{{ $slot }}</div>
