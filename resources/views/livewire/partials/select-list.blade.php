@php
    $groups = collect($this->choices)->chunkWhile(fn ($option, $key, $chunk) =>
        ($option['group'] ?? null) === ($chunk->last()['group'] ?? null) && ! ($option['separatorBefore'] ?? false));
@endphp
@forelse ($groups as $group)
    @if ($group->first()['separatorBefore'] ?? false)
        <div data-esoboy-ui data-slot="select-separator" role="separator"></div>
    @endif
    <div data-esoboy-ui data-slot="select-group" role="group" @isset($group->first()['group']) aria-label="{{ $group->first()['group'] }}" @endisset>
        @isset($group->first()['group'])<div data-esoboy-ui data-slot="option-group-label" role="presentation">{{ $group->first()['group'] }}</div>@endisset
        @include('ui::livewire.partials.options', ['choices' => $group, 'popup' => true, 'showGroupLabels' => false])
    </div>
@empty
    @include('ui::partials.empty-state', ['emptyMessage' => 'options'])
@endforelse
