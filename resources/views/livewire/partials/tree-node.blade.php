@php
    $selected = $this->isSelected($row['value']);
    $selectionDisabled = $disabled || $readonly || $row['disabled'];
    $argument = \Illuminate\Support\Js::from($row['value']);
@endphp
@if ($row['branch'] && $selectParents)
    <x-ui::button class="ui-node-selector" variant="ghost" :size="\Esoboy\Ui\Support\ComponentSize::icon($size)" :role="$multiple ? 'checkbox' : 'radio'"
        :aria-checked="$selected ? 'true' : 'false'" :disabled="$selectionDisabled"
        x-on:keydown.stop="navigate($event, $el.parentElement.querySelector('[data-node-action]'))"
        :aria-label="__('ui::ui.select_node', ['label' => $row['label']])" :wire:click="'choose('.$argument.')'">
        <span aria-hidden="true">
            @if ($selected && $multiple)<x-ui::icon name="check" />@endif
        </span>
    </x-ui::button>
@endif
<x-ui::button variant="ghost" :size="$size" class="ui-tree-action" data-node-action
    :wire:click="($row['branch'] ? 'expand' : 'choose').'('.$argument.')'"
    :disabled="$row['branch'] ? $browseDisabled : $selectionDisabled"
    :aria-expanded="$row['branch'] ? ($active ? 'true' : 'false') : null"
    :aria-pressed="$row['branch'] ? null : ($selected ? 'true' : 'false')">
    <span>{{ $row['label'] }}</span>
    @if ($row['branch'])
        <x-ui::icon :name="$active && $collapsible ? 'chevron-down' : 'chevron-right'" />
    @elseif ($selected)
        <x-ui::icon name="check" />
    @endif
</x-ui::button>
