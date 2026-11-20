@blaze
@props(['value', 'name', 'multiple' => false, 'checked' => false, 'disabled' => false, 'invalid' => false, 'shortcut' => ''])
<label data-esoboy-ui data-slot="questionnaire-choice" @if($disabled) data-disabled @endif>
    <input {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'questionnaire-choice-input', 'type' => $multiple ? 'checkbox' : 'radio', 'name' => $name, 'value' => $value, 'aria-keyshortcuts' => $shortcut ?: null, 'data-shortcut' => $shortcut ?: null, 'aria-invalid' => $invalid ? 'true' : null]) }} @checked($checked) @disabled($disabled)>
    <span data-esoboy-ui data-slot="questionnaire-choice-indicator" aria-hidden="true"><span></span><x-ui::icon name="check" /></span>
    <span data-esoboy-ui data-slot="questionnaire-choice-label">{{ $slot }}</span>
    @if ($shortcut !== '')<kbd data-esoboy-ui data-slot="questionnaire-shortcut">{{ $shortcut }}</kbd>@endif
</label>
