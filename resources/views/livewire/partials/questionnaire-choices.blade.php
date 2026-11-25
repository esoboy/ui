            <x-ui::questionnaire.choices>
                @foreach ($question['choices'] as $choice)
                    @php($shortcut = $shortcuts !== '' && !$choice['disabled'] ? ($shortcuts === 'letters' ? (range('A', 'Z')[$shortcutIndex++] ?? '') : (range(1, 9)[$shortcutIndex++] ?? '')) : '')
                    <x-ui::questionnaire.choice wire:key="choice-{{ $uid }}-{{ $answerName }}-{{ $choice['value'] }}" :form="$attributes->get('form')" :name="$answerName" :value="$choice['value']" :multiple="$question['multiple']"
                        :checked="in_array($choice['value'], $answer['choices'], true)" :disabled="$disabled || $readonly || $choice['disabled']"
                        :invalid="$hasError" :shortcut="(string) $shortcut" :wire:change="'choose('.\Illuminate\Support\Js::from($choice['value']).')'">
                        <span class="{{ $choice['labelClass'] }}">{{ $choice['label'] }}</span>
                        @if ($choice['description'] !== '')<x-ui::questionnaire.choice-description>{{ $choice['description'] }}</x-ui::questionnaire.choice-description>@endif
                    </x-ui::questionnaire.choice>
                @endforeach
                @if ($question['input'])
                    <x-ui::questionnaire.input :form="$attributes->get('form')" :name="! $readonly && $answer['text'] !== '' ? $answerName : null" :value="$answer['text']"
                        :aria-label="$question['input']['label']" :placeholder="$question['input']['placeholder']"
                        :aria-invalid="$hasError ? 'true' : null" :disabled="$disabled" :readonly="$readonly"
                        wire:change="writeText($event.target.value)" />
                @endif
            </x-ui::questionnaire.choices>
            @if ($hasError)
                <x-ui::questionnaire.error id="{{ $uid }}-error">{{ $question['error'] ?: __('ui::ui.questionnaire_required') }}</x-ui::questionnaire.error>
            @endif
