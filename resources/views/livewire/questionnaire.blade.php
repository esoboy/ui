<form data-esoboy-ui data-slot="questionnaire" novalidate wire:submit="submit" x-data="uiQuestionnaire"
    x-on:keydown="navigate($event)" x-on:reset.prevent="$wire.resetAnswers()"
    x-on:questionnaire-focus.stop="focusItem($event.detail.invalid)"
    {{ $attributes->whereDoesntStartWith('wire:model')->except('name') }}>
    @php
        $questions = $this->questions;
        $position = $this->position;
        $answers = $this->answers;
        $question = $questions[$position] ?? null;
        $uid = $this->getId();
    @endphp
    @if ($question)
        @php
            $answer = $answers[$question['name']];
            $hasError = $invalid === $question['name'] || $question['error'] !== '';
            $answerName = $question['name'];
            $shortcutIndex = 0;
        @endphp
        @if($layout === 'card')
            <x-ui::questionnaire.item wire:key="question-{{ $question['name'] }}" :disabled="$disabled" :data-status="$answer['status']" :aria-invalid="$hasError ? 'true' : null" aria-labelledby="{{ $uid }}-title">
                <x-ui::card>
                    <x-ui::card.header>
                        <x-ui::card.title id="{{ $uid }}-title" data-slot="questionnaire-title">{{ $question['prompt'] }}</x-ui::card.title>
                        @if($question['description'] !== '')<x-ui::card.description data-slot="questionnaire-description">{{ $question['description'] }}</x-ui::card.description>@endif
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
            @continue((! $readonly && $name === $question['name']) || $previous['status'] !== 'answered')
            @foreach ([...$previous['choices'], ...($previous['text'] !== '' ? [$previous['text']] : [])] as $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" @disabled($disabled)>
            @endforeach
        @endforeach
    @else
        @include('ui::partials.empty-state', ['emptyMessage' => 'questions', 'emptySize' => 'default', 'emptyIcon' => 'list-checks'])
    @endif
</form>
