@aware(['disabled' => false, 'collapsible' => true])
<summary {{ $attributes->class('group/accordion-trigger')->merge(['data-esoboy-ui' => '', 'data-slot' => 'accordion-summary']) }} @if($disabled) inert aria-disabled="true" @endif
    @if(!$collapsible) x-on:click="if ($el.parentElement.open) $event.preventDefault()" @endif>{{ $slot }}<x-ui::icon name="chevron-down" data-slot="accordion-trigger-icon" data-state="closed" /><x-ui::icon name="chevron-up" data-slot="accordion-trigger-icon" data-state="open" /></summary>
