@php
    $groups = collect($this->choices)->chunkWhile(fn ($option, $key, $chunk) =>
        ($option['group'] ?? null) === ($chunk->last()['group'] ?? null) && ! ($option['separatorBefore'] ?? false));
@endphp
@forelse ($groups as $group)
    @php($groupKey = md5(serialize($group->first()['value'])))
    @if ($group->first()['separatorBefore'] ?? false)
        <div wire:key="before-{{ $groupKey }}" class="ui-select-separator" role="separator"></div>
    @endif
    <div wire:key="select-group-{{ $groupKey }}" class="ui-select-group" role="group" @isset($group->first()['group']) aria-label="{{ $group->first()['group'] }}" @endisset>
        @isset($group->first()['group'])<div wire:key="label-{{ $groupKey }}" class="ui-option-group-label" role="presentation">{{ $group->first()['group'] }}</div>@endisset
        @include('ui::livewire.partials.options', ['choices' => $group, 'clientSelect' => true, 'showGroupLabels' => false])
    </div>
@empty
    @include('ui::partials.empty-state', ['emptyMessage' => 'options'])
@endforelse
