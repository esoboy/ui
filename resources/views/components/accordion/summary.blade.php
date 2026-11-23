@aware(['disabled' => false, 'collapsible' => true])
<summary {{ $attributes->class('ui-accordion-summary group/accordion-trigger') }} @if($disabled) inert aria-disabled="true" @endif
    @if(!$collapsible) x-on:click="if ($el.parentElement.open) $event.preventDefault()" @endif>{{ $slot }}<x-ui::icon class="ui-accordion-trigger-icon" name="chevron-down" data-state="closed" /><x-ui::icon class="ui-accordion-trigger-icon" name="chevron-up" data-state="open" /></summary>
