<form {{ $attributes->whereDoesntStartWith('wire:model')->merge(['data-slot' => 'questionnaire', 'novalidate' => true, 'data-shortcuts' => $shortcuts, 'x-data' => 'uiQuestionnaire', 'x-on:keydown' => 'key($event)', 'x-on:ui-questionnaire-step' => '$nextTick(() => focus())']) }} wire:submit="submit">
    <div role="progressbar" aria-label="{{ __('ui::questionnaire.progress_label') }}" aria-live="polite" aria-valuemin="1" aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $index + 1 }}" data-slot="questionnaire-progress">{{ count($steps) ? __('ui::questionnaire.progress', ['current' => $index + 1, 'total' => count($steps)]) : '' }}</div>
    @foreach ($items as $item)
    @php
        $active = $current === $item['name'] && empty($item['disabled']);
        $invalid = in_array($item['name'], $attempted, true) && ! $this->settled($item);
        $multiple = $item['multiple'] ?? false;
        $options = \Esoboy\Ui\Support\Options::normalize($item['options'] ?? []);
        $keys = $shortcuts === 'letters' ? range('A', 'Z') : ($shortcuts === 'numbers' ? range(1, 9) : []);
        $prefix = $this->getId().'-'.$item['name'];
    @endphp
    <fieldset data-slot="questionnaire-item" data-name="{{ $item['name'] }}" @if ($item['required'] ?? false) data-required @endif @if ($multiple) data-multiple @endif @if ($active) data-active @else hidden inert @endif @disabled($item['disabled'] ?? false) tabindex="-1" @if ($invalid) aria-invalid="true" @endif aria-labelledby="{{ $prefix }}-title" aria-describedby="{{ $prefix }}-description{{ $invalid ? ' '.$prefix.'-error' : '' }}" wire:key="question-{{ $item['name'] }}">
        <x-ui::questionnaire.title :id="$prefix.'-title'">{{ $questionView ? view($questionView, ['item' => $item, 'active' => $active]) : $item['title'] }}</x-ui::questionnaire.title>
        @if (isset($item['description']))<p data-slot="questionnaire-description" id="{{ $prefix }}-description">{{ $item['description'] }}</p>@endif
        <div role="{{ $multiple ? 'group' : 'radiogroup' }}" data-slot="questionnaire-choices">
            @foreach ($options as $option)
            @php($source = collect($item['options'])->firstWhere('value', $option['value']) ?? [])
            <label data-slot="questionnaire-choice" data-type="{{ $multiple ? 'checkbox' : 'radio' }}" @if (isset($keys[$loop->index])) data-shortcut="{{ $keys[$loop->index] }}" @endif>
                <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="{{ $prefix }}{{ $multiple ? '[]' : '' }}" value="{{ $option['value'] }}" data-slot="questionnaire-choice-input" wire:click="choose({{ \Illuminate\Support\Js::from($item['name']) }}, {{ \Illuminate\Support\Js::from($option['value']) }})" @checked(in_array($option['value'], (array) ($value[$item['name']] ?? []), true)) @disabled($option['disabled'] ?? false) @if (isset($keys[$loop->index])) aria-keyshortcuts="{{ $keys[$loop->index] }}" @endif>
                <span aria-hidden="true" data-slot="questionnaire-choice-indicator"><span data-slot="questionnaire-choice-indicator-dot"></span><x-ui::icon name="check" data-slot="questionnaire-choice-indicator-check" /></span>
                <span data-slot="questionnaire-choice-label"><span @class([$source['class'] ?? ''])>{{ $option['label'] }}</span>@if (isset($source['description']))<x-ui::questionnaire.choice-description>{{ $source['description'] }}</x-ui::questionnaire.choice-description>@endif</span>
                @if (isset($keys[$loop->index]))<x-ui::kbd data-questionnaire-choice-shortcut aria-hidden="true">{{ $keys[$loop->index] }}</x-ui::kbd>@endif
            </label>
            @endforeach
            @if ($item['input'] ?? false)<div data-slot="questionnaire-input-wrapper"><x-ui::input wire:model.live="value.{{ $item['other'] ?? $item['name'] }}" data-questionnaire-input :aria-label="$item['inputLabel'] ?? $item['title']" :placeholder="$item['placeholder'] ?? null" class="min-h-11 sm:min-h-0" /></div>@endif
        </div>
        <p data-slot="questionnaire-error" id="{{ $prefix }}-error" @if (! $invalid) hidden @else role="alert" @endif>{{ ($item['required'] ?? false) ? __('ui::questionnaire.required') : __('ui::questionnaire.optional') }}</p>
    </fieldset>
    @endforeach
    @if ($slots->has('default')){{ $slots }}@endif
    <x-ui::questionnaire.actions>
        @if ($slots->has('actions'))
            {{ $slots('actions') }}
        @else
        <x-ui::button variant="outline" wire:click="previous" data-questionnaire="previous" :hidden="$index < 1">{{ __('ui::questionnaire.previous') }}</x-ui::button>
        <x-ui::button variant="outline" wire:click="skip" data-questionnaire="skip" :hidden="! count($steps) || ($steps[$index]['required'] ?? false)">{{ __('ui::questionnaire.skip') }}</x-ui::button>
        <x-ui::button wire:click="next" data-questionnaire="next" :hidden="$index >= count($steps) - 1">{{ __('ui::questionnaire.next') }}</x-ui::button>
        <x-ui::button type="submit" data-questionnaire="submit" :hidden="$index !== count($steps) - 1">{{ __('ui::questionnaire.submit') }}</x-ui::button>
        @endif
    </x-ui::questionnaire.actions>
</form>
