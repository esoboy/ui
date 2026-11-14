@php($labelling = ['id', 'aria-label', 'aria-labelledby'])
<eso-tags-input {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', ...$labelling])->merge(['data-slot' => 'tags-input', 'data-disabled' => (bool) ($settings['disabled'] ?? false), 'x-data' => '{}', 'x-on:click.self' => '$refs.entry.focus()']) }}>
    @foreach ($value as $tag)
    <span data-slot="tags-input-tag" wire:key="tag-{{ md5($tag) }}">
        <span data-tags-input-label>{{ isset($settings['tagView']) ? view($settings['tagView'], ['tag' => $tag]) : $tag }}</span>
        <x-ui::button variant="ghost" size="icon-xs" data-tags-input-remove :aria-label="__('ui::common.remove')" tabindex="-1" :wire:click="'remove('.\Illuminate\Support\Js::from($tag).')'" :disabled="$settings['disabled'] ?? false" x-on:click="$nextTick(() => $refs.entry.focus())"><x-ui::icon name="x" /></x-ui::button>
    </span>
    @endforeach
    <input type="text" data-slot="tags-input-input" x-ref="entry" wire:model="entry" wire:keydown.enter.prevent="add" wire:blur="add" x-on:keydown="if (!$event.isComposing && $event.key === ',') { $event.preventDefault(); $wire.add() } else if ($event.key === 'Backspace' && !$el.value) $wire.removeLast()" x-on:input="if (/[,，\n]/.test($el.value)) $wire.add()" placeholder="{{ $settings['placeholder'] ?? '' }}" autocomplete="off" {{ $attributes->only($labelling)->merge($settings['validation'] ?? []) }} @disabled(($settings['disabled'] ?? false) || (($settings['max'] ?? null) && count($value) >= $settings['max']))>
    @if ($slots->has('default')){{ $slots }}@endif
    @include('ui::livewire.form-value')
</eso-tags-input>
