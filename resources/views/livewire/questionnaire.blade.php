<div {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes, 'resetAnswers') }} role="group" x-data="uiQuestionnaire"
    x-on:keydown="navigate($event)"
    x-on:questionnaire-focus.stop="focusItem($event.detail.invalid)"
    {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->class('ui-questionnaire-body') }}>
    @php
        $questions = $this->questions;
        $position = $this->position;
        $answers = $this->answers;
        $question = $questions[$position] ?? null;
        $uid = $this->getId();
    @endphp
    @include('ui::livewire.partials.model-field')
    @if ($question)
        @php
            $answer = $answers[$question['name']];
            $hasError = $invalid === $question['name'] || $question['error'] !== '';
            $answerName = $question['name'];
            $fieldName = $answerName.($question['multiple'] ? '[]' : '');
            $shortcutIndex = 0;
        @endphp
        @if($layout === 'card')
            <x-ui::questionnaire.item wire:key="question-{{ $question['name'] }}" :disabled="$disabled" :data-status="$answer['status']" :aria-invalid="$hasError ? 'true' : null" aria-labelledby="{{ $uid }}-title">
                <x-ui::card>
                    <x-ui::card.header>
                        <x-ui::card.title class="ui-questionnaire-title" id="{{ $uid }}-title">{{ $question['prompt'] }}</x-ui::card.title>
                        @if($question['description'] !== '')<x-ui::card.description class="ui-questionnaire-description">{{ $question['description'] }}</x-ui::card.description>@endif
                        @if($showProgress)<x-ui::card.action><x-ui::questionnaire.progress :current="$position + 1" :total="count($questions)" /></x-ui::card.action>@endif
                    </x-ui::card.header>
                    <x-ui::card.content>@include('ui::livewire.partials.questionnaire-choices')</x-ui::card.content>
                    <x-ui::card.footer>@include('ui::livewire.partials.questionnaire-actions')</x-ui::card.footer>
                </x-ui::card>
            </x-ui::questionnaire.item>
        @else
            @if($layout === 'dialog')<x-ui::dialog.header>
                @if($showProgress)<x-ui::questionnaire.progress :class="$progressClass" :current="$position + 1" :total="count($questions)" />@endif
            </x-ui::dialog.header>
            @elseif($showProgress)<x-ui::questionnaire.progress :class="$progressClass" :current="$position + 1" :total="count($questions)" />@endif
            <x-ui::questionnaire.item wire:key="question-{{ $question['name'] }}" :disabled="$disabled" :data-status="$answer['status']" :aria-invalid="$hasError ? 'true' : null" aria-describedby="{{ $uid }}-description {{ $uid }}-error">
                <x-ui::questionnaire.title>{{ $question['prompt'] }}</x-ui::questionnaire.title>
                @if($question['description'] !== '')<x-ui::questionnaire.description id="{{ $uid }}-description">{{ $question['description'] }}</x-ui::questionnaire.description>@endif
                @include('ui::livewire.partials.questionnaire-choices')
            </x-ui::questionnaire.item>
            @if($layout === 'dialog')<x-ui::dialog.footer>@include('ui::livewire.partials.questionnaire-actions')</x-ui::dialog.footer>
            @else @include('ui::livewire.partials.questionnaire-actions') @endif
        @endif
        @foreach ($answers as $name => $previous)
            @continue(($mode !== 'controlled' && ! $readonly && $name === $question['name']) || $previous['status'] !== 'answered')
            @foreach ([...$previous['choices'], ...($previous['text'] !== '' ? [$previous['text']] : [])] as $value)
                <input type="hidden" wire:key="answer-{{ $uid }}-{{ $name }}-{{ $loop->index }}" {{ $attributes->only('form') }} name="{{ $name }}{{ collect($questions)->firstWhere('name', $name)['multiple'] ? '[]' : '' }}" value="{{ $value }}" @disabled($disabled)>
            @endforeach
        @endforeach
    @else
        @include('ui::partials.empty-state', ['emptyMessage' => 'questions', 'emptySize' => 'default', 'emptyIcon' => 'list-checks'])
    @endif
</div>
