@php
    $requiredField ??= (bool) $attributes->get('required');
@endphp
@include('ui::livewire.partials.model-field', ['formField' => $mode === 'bound', 'requiredField' => $requiredField])
@if ($mode !== 'bound' && ($attributes->has('name') || $requiredField))
    @php
        $values = array_values($selection ?? $this->selected);
        $initial = $mode === 'controlled' ? $this->current : $this->initialFormValue;
        $defaults = is_array($initial) ? array_values($initial) : ($initial === null ? [] : [$initial]);
        $many = is_array($this->current) || is_array($initial);
        $count = max(($requiredField && ($range ?? false)) ? 2 : 1, count($values), count($defaults));
        $property = $mode === 'controlled' ? 'externalValue' : 'value';
    @endphp
    {{-- Each native field retains its own reset value, including repeated range endpoints. --}}
    @for ($i = 0; $i < $count; $i++)
        @php
            $default = $defaults[$i] ?? null;
            $current = $values[$i] ?? null;
            $options = array_unique(array_filter([$current, $default, ...($pendingChoices ?? []), ...(($this instanceof \Esoboy\Ui\Livewire\Combobox && ! ($this instanceof \Esoboy\Ui\Livewire\IconSelect)) ? array_column($this->choices, 'value') : [])], static fn ($v) => $v !== null));
        @endphp
        <select multiple tabindex="-1" aria-hidden="true" class="ui-visually-hidden" @disabled($disabled)
            @if ($attributes->has('name')) name="{{ $attributes->get('name') }}{{ $many ? '[]' : '' }}" @endif
            {{ $attributes->only('form') }}
            @if ($requiredField ?? false) @required(! $readonly && ($i === 0 || ($range ?? false))) x-on:invalid.prevent="$el.parentElement.querySelector('button:not(:disabled), input:not([type=hidden])')?.focus()" @endif
            wire:key="form-value-{{ $i }}-{{ md5(serialize([$options, $many])) }}"
            wire:bind:value="{{ $many ? $property.'?.['.$i.']' : $property }} ?? []">
            @foreach ($options as $option)
                <option value="{{ $option }}" @selected($option === $default)>{{ $option }}</option>
            @endforeach
        </select>
    @endfor
@endif
