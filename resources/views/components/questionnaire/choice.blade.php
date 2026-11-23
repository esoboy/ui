@blaze
@props(['value', 'name', 'multiple' => false, 'checked' => false, 'disabled' => false, 'invalid' => false, 'shortcut' => ''])
<label class="ui-questionnaire-choice" @if($disabled) data-disabled @endif>
    <input {{ $attributes->merge(['class' => 'ui-questionnaire-choice-input', 'type' => $multiple ? 'checkbox' : 'radio', 'name' => $name, 'value' => $value, 'aria-keyshortcuts' => $shortcut ?: null, 'data-shortcut' => $shortcut ?: null, 'aria-invalid' => $invalid ? 'true' : null]) }} @checked($checked) @disabled($disabled)>
    <span class="ui-questionnaire-choice-indicator" aria-hidden="true"><span></span><x-ui::icon name="check" /></span>
    <span class="ui-questionnaire-choice-label">{{ $slot }}</span>
    @if ($shortcut !== '')<kbd class="ui-questionnaire-shortcut">{{ $shortcut }}</kbd>@endif
</label>
