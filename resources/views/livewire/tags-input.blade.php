    @php
        $edit = $mode === 'controlled' ? ['wire:model' => 'draft', 'wire:keydown.enter.prevent' => 'add'] : [
                'data-ui-tags' => $this->fieldId(), 'data-limit' => $max,
                'x-on:keydown.enter' => 'if ($event.isComposing) return; $event.preventDefault(); if ($el.readOnly || $el.disabled) return; const field = document.getElementById($el.dataset.uiTags), tags = field.value ?? [], tag = $el.value.trim(); if (tag && Array.from(tag).length <= $el.maxLength && !tags.includes(tag) && (!$el.dataset.limit || tags.length < Number($el.dataset.limit))) { field.edit([...tags, tag]); $el.value = \'\'; }',
            ];
        $input = $attributes->only(['id', 'placeholder', 'form'])->merge($attributes->whereStartsWith('aria-')->all())->merge($edit);
    @endphp
<div {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes) }}
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'form', 'placeholder', 'required'])->class('ui-tags-input-body') }}>
    <div class="ui-tags" x-init="let focused; const root = $el; $wire.$hook('morph', () => { focused = root.contains(document.activeElement) ? document.activeElement : null }); $wire.$hook('morphed', () => { if (focused?.isConnected && document.activeElement === document.body) focused.focus() })" x-on:click.capture="const button = $event.target.closest('button'); if (button && button === document.activeElement) { const tag = button.closest('[data-tag]'); (tag.nextElementSibling?.querySelector('button') ?? tag.previousElementSibling?.querySelector('button') ?? $el.parentElement.querySelector('input'))?.focus() }">
        @php($occurrences = [])
        @foreach ($this->tags as $index => $tag)
            @php($occurrences[$tag] = ($occurrences[$tag] ?? 0) + 1)
            <span data-tag wire:key="tag-{{ $this->getId() }}-{{ md5($tag) }}-{{ $occurrences[$tag] }}"><x-ui::badge :size="$size" variant="secondary">{{ $tag }}<x-ui::button
                    variant="ghost" size="icon-xs" :disabled="$disabled || $readonly"
                    :data-tag-value="$tag" :data-occurrence="$occurrences[$tag]"
                    :attributes="$mode === 'controlled'
                        ? new \Illuminate\View\ComponentAttributeBag(['wire:click' => 'remove('.\Illuminate\Support\Js::from($tag).', '.$index.')'])
                        : new \Illuminate\View\ComponentAttributeBag(['data-field' => $this->fieldId(), 'x-on:click' => 'const field = document.getElementById($el.dataset.field); let occurrence = 0; field.edit(field.value.filter(tag => tag !== $el.dataset.tagValue || ++occurrence !== Number($el.dataset.occurrence)))'])"
                    :aria-label="__('ui::ui.remove_item', ['label' => $tag])"><x-ui::icon
                        name="x" :size="12" /></x-ui::button></x-ui::badge></span>
        @endforeach
    </div>
    @include('ui::livewire.partials.selection-input', ['selection' => $this->tags, 'requiredField' => (bool) $attributes->get('required')])
    <x-ui::input :size="$size" :attributes="$input" :disabled="$disabled" :readonly="$readonly"
        :aria-required="$attributes->get('required') ? 'true' : null" :maxlength="$maxLength" />
</div>
