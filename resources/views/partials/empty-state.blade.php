@php
    $translation = 'ui::ui.empty_states.'.$emptyMessage;
    $title = filled($emptyTitle ?? null) ? $emptyTitle : __($translation.'.title');
    $descriptionKey = $translation.'.description';
    $description = $emptyDescription ?? (\Illuminate\Support\Facades\Lang::has($descriptionKey) ? __($descriptionKey) : null);
@endphp
<x-ui::empty :size="$emptySize ?? 'sm'" role="status">
    <x-ui::empty.header>
        @isset($emptyIcon)
            <x-ui::empty.media variant="icon"><x-ui::icon :name="$emptyIcon" /></x-ui::empty.media>
        @endisset
        <x-ui::empty.title>{{ $title }}</x-ui::empty.title>
        @if (filled($description))
            <x-ui::empty.description>{{ $description }}</x-ui::empty.description>
        @endif
    </x-ui::empty.header>
</x-ui::empty>
