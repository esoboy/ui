<eso-emoji-picker {{ $attributes->whereDoesntStartWith('wire:model')->merge(['data-slot' => 'emoji-picker', 'data-columns' => $columns, 'style' => '--emoji-picker-columns: '.$columns.';', 'x-data' => 'uiEmojiPicker', 'x-on:keydown' => 'key($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:pointerleave' => 'activate(null)', 'x-on:emoji-select' => 'close()', 'x-effect' => '$wire.query; $nextTick(() => refresh())']) }}>
    <x-ui::emoji-picker.search wire:model.live.debounce.150ms="query" />
    <div data-slot="emoji-picker-viewport" tabindex="0">
        <div data-slot="emoji-picker-loading" hidden><x-ui::icon name="loader" /></div>
        <div data-slot="emoji-picker-empty" @if ($categories !== []) hidden @endif>{{ __('ui::emoji-picker.empty') }}</div>
        <div data-slot="emoji-picker-list" role="listbox" aria-label="{{ __('ui::emoji-picker.label') }}">
            @foreach ($categories as $category => $emojis)
            <div data-slot="emoji-picker-category" data-category="{{ $category }}" role="group" aria-label="{{ __('ui::emoji-picker.'.$category) }}" style="--emoji-picker-rows: {{ ceil(count($emojis) / max(1, $columns)) }};" wire:key="emoji-category-{{ $category }}">
                <div data-slot="emoji-picker-category-header" aria-hidden="true">{{ __('ui::emoji-picker.'.$category) }}</div>
                <div data-slot="emoji-picker-emojis">
                    @foreach ($emojis as [$emoji, $label, $keywords, $version])
                    <button type="button" role="option" aria-selected="false" tabindex="-1" data-slot="emoji-picker-emoji" data-version="{{ $version }}" aria-label="{{ $label }}" wire:click="pick('{{ $emoji }}')" wire:key="emoji-{{ md5($emoji) }}">{{ $emoji }}</button>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @if ($slots->has('footer')){{ $slots('footer') }}@elseif ($footer)<x-ui::emoji-picker.footer />@endif
    @if ($slots->has('default')){{ $slots }}@endif
</eso-emoji-picker>
