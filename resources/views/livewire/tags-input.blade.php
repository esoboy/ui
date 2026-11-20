<div data-esoboy-ui data-slot="tags-input"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby']) }}>
    <div class="ui-tags">
        @foreach ($this->tags as $tag)
            <x-ui::badge variant="secondary" wire:key="tag-{{ $tag }}">{{ $tag }}<x-ui::button
                    variant="ghost" size="icon-xs" :disabled="$disabled || $readonly"
                    :wire:click="'remove('.\Illuminate\Support\Js::from($tag).')'" :aria-label="__('ui::ui.remove_item', ['label' => $tag])"><x-ui::icon
                        name="x" :size="12" /></x-ui::button></x-ui::badge>
            @if ($attributes->get('name'))
                <input type="hidden" name="{{ $attributes->get('name') }}[]" {{ $attributes->only('form') }} value="{{ $tag }}"
                    @disabled($disabled)>
            @endif
        @endforeach
    </div>
    <x-ui::input wire:model="draft" wire:keydown.enter.prevent="add" :disabled="$disabled" :readonly="$readonly"
        :maxlength="$maxLength" {{ $attributes->only('id')->merge($attributes->whereStartsWith('aria-')->all()) }} />
</div>
