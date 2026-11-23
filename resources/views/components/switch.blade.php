@blaze
@props(['status' => null, 'checked' => false, 'invalid' => false, 'size' => 'default'])
<span data-size="{{ $size }}" aria-invalid="{{ $invalid || $attributes->get('aria-invalid') === 'true' ? 'true' : 'false' }}" {{ $attributes->only('class')->class(['ui-switch group/switch', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>
    <input {{ $attributes->except('class')->merge(['type' => 'checkbox', 'role' => 'switch', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }} @checked($checked)>
    <span class="ui-switch-thumb"></span>
</span>
