        <x-ui::questionnaire.actions>
            @if ($position > 0)<x-ui::questionnaire.previous><x-ui::button variant="outline" :disabled="$disabled">{{ __('ui::ui.previous') }}</x-ui::button></x-ui::questionnaire.previous>@endif
            @if (!$question['required'])<x-ui::questionnaire.skip><x-ui::button variant="outline" :disabled="$disabled || $readonly">{{ __('ui::ui.skip') }}</x-ui::button></x-ui::questionnaire.skip>@endif
            @if ($position < count($questions) - 1)
                <x-ui::questionnaire.next><x-ui::button :disabled="$disabled">{{ __('ui::ui.next') }}</x-ui::button></x-ui::questionnaire.next>
            @else
                <x-ui::questionnaire.submit><x-ui::button :disabled="$disabled || $readonly">{{ $submitLabel ?: __('ui::ui.submit') }}</x-ui::button></x-ui::questionnaire.submit>
            @endif
        </x-ui::questionnaire.actions>
