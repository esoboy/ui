@blaze

<div data-slot="command-input-wrapper">
    <x-ui::input-group>
        <x-ui::input-group.input :attributes="$attributes->merge(['data-command-input' => true, 'class' => 'p-0 text-sm', 'autocomplete' => 'off', 'spellcheck' => 'false', 'role' => 'combobox', 'aria-expanded' => 'true', 'aria-autocomplete' => 'list'])" />
        <x-ui::input-group.addon>
            <x-ui::icon name="search" />
        </x-ui::input-group.addon>
    </x-ui::input-group>
</div>
