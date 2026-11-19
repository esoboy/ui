@blaze
@props(['checked' => false, 'invalid' => false, 'size' => 'default'])
<span data-slot="switch" data-size="{{ $size }}" aria-invalid="{{ $invalid || $attributes->get('aria-invalid') === 'true' ? 'true' : 'false' }}" class="ui-switch group/switch {{ $attributes->get('class') }}">
    <input {{ $attributes->except('class')->merge(['type' => 'checkbox', 'role' => 'switch', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }} @checked($checked)>
    <span data-slot="switch-thumb"></span>
</span>
