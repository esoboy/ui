            <x-ui::questionnaire.choices data-question="{{ $answerName }}" data-answers="{{ json_encode($answers, JSON_THROW_ON_ERROR) }}"
                :data-exclusive="! $question['multiple'] && $question['input'] ? true : null"
                :data-empty-answer="json_encode($this->answerValue([], ''), JSON_THROW_ON_ERROR)"
                :data-filled-status="$this::ANSWERED">
                @foreach ($question['choices'] as $choice)
                    @php($shortcut = $shortcuts !== '' && !$choice['disabled'] ? ($shortcuts === 'letters' ? (range('A', 'Z')[$shortcutIndex++] ?? '') : (range(1, 9)[$shortcutIndex++] ?? '')) : '')
                    <x-ui::questionnaire.choice wire:key="choice-{{ $uid }}-{{ $answerName }}-{{ $choice['value'] }}" :form="$attributes->get('form')" :name="$mode === 'controlled' ? null : $fieldName" :value="$choice['value']" :multiple="$question['multiple']"
                        :checked="in_array($choice['value'], $answer['choices'], true)" :disabled="$disabled || $readonly || $choice['disabled']"
                        :invalid="$hasError" :shortcut="(string) $shortcut" :attributes="new \Illuminate\View\ComponentAttributeBag($mode === 'controlled' ? ['wire:click.prevent' => 'choose('.\Illuminate\Support\Js::from($choice['value']).')'] : ['x-on:change' => 'readAnswer($event.target)'])">
                        <span class="{{ $choice['labelClass'] }}">{{ $choice['label'] }}</span>
                        @if ($choice['description'] !== '')<x-ui::questionnaire.choice-description>{{ $choice['description'] }}</x-ui::questionnaire.choice-description>@endif
                    </x-ui::questionnaire.choice>
                @endforeach
                @if ($question['input'])
                    @if ($mode !== 'controlled')
                        <input hidden type="text" data-answer-model {{ $this->fieldBinding() }}>
                    @endif
                    <x-ui::questionnaire.input :form="$attributes->get('form')" :name="$mode !== 'controlled' && ! $readonly && ($question['choices'] === [] || $answer['text'] !== '') ? $fieldName : null" :optional-name="$mode === 'controlled' || $readonly || $question['choices'] === [] ? null : $fieldName" :value="$answer['text']"
                        :aria-label="$question['input']['label']" :placeholder="$question['input']['placeholder']"
                        :aria-invalid="$hasError ? 'true' : null" :disabled="$disabled" :readonly="$readonly"
                        :attributes="new \Illuminate\View\ComponentAttributeBag($mode === 'controlled' ? ['wire:input.debounce.150ms' => 'writeText($event.target.value)', 'x-on:questionnaire-restore.window' => 'restoreText($event, $el)'] : ['x-on:input' => 'if (!$event.isComposing) readAnswer($el)', 'x-on:compositionend' => 'readAnswer($el)', 'x-on:change' => 'readAnswer($el, \'change\')', 'x-on:blur' => 'readAnswer($el, \'blur\')'])" />
                @endif
            </x-ui::questionnaire.choices>
            @if ($hasError)
                <x-ui::questionnaire.error id="{{ $uid }}-error">{{ $question['error'] ?: __('ui::ui.questionnaire_required') }}</x-ui::questionnaire.error>
            @endif
