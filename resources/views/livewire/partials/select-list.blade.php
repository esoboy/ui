@php
    $groups = collect($this->choices)->chunkWhile(fn ($option, $key, $chunk) =>
        ($option['group'] ?? null) === ($chunk->last()['group'] ?? null) && ! ($option['separatorBefore'] ?? false));
@endphp
@forelse ($groups as $group)
    @if ($group->first()['separatorBefore'] ?? false)
        <div class="ui-select-separator" role="separator"></div>
    @endif
    <div class="ui-select-group" role="group" @isset($group->first()['group']) aria-label="{{ $group->first()['group'] }}" @endisset>
        @isset($group->first()['group'])<div class="ui-option-group-label" role="presentation">{{ $group->first()['group'] }}</div>@endisset
        @include('ui::livewire.partials.options', ['choices' => $group, 'clientSelect' => true, 'showGroupLabels' => false])
    </div>
@empty
    @include('ui::partials.empty-state', ['emptyMessage' => 'options'])
@endforelse
