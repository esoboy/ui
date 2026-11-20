@props(['invalid' => false])
<div data-esoboy-ui data-slot="password-input" x-data="{ revealed: false }">
    <x-ui::input-group>
        <x-ui::input-group.input type="password" x-bind:type="revealed ? 'text' : 'password'" :invalid="$invalid" {{ $attributes }} />
        <x-ui::input-group.addon align="inline-end">
            <x-ui::input-group.button size="icon-xs" :disabled="$attributes->get('disabled', false)" x-on:click="revealed = !revealed" x-bind:aria-pressed="revealed"
                aria-label="{{ __('ui::ui.password_visibility') }}"><x-ui::icon name="eye" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
</div>
