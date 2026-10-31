@blaze

<div data-slot="command-input-wrapper">
    <div data-slot="input-group" role="group">
        <input {{ $attributes->merge(['type' => 'text', 'data-slot' => 'command-input', 'autocomplete' => 'off', 'spellcheck' => 'false', 'role' => 'combobox', 'aria-expanded' => 'true', 'aria-autocomplete' => 'list']) }}>
        <div data-slot="input-group-addon" data-align="inline-start" role="group">
            <x-ui::icon name="search" />
        </div>
    </div>
</div>
