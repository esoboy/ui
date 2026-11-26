@props(['size' => 'default', 'invalid' => false])
<div {{ $attributes->only(['class', 'wire:key'])->class('ui-password-input') }} x-data="uiPassword">
    <x-ui::input-group :size="is_numeric($size) ? 'default' : $size">
        <x-ui::input-group.input :size="$size" type="password" x-bind:type="revealed ? 'text' : 'password'" :invalid="$invalid" {{ $attributes->except(['class', 'wire:key']) }} />
        <x-ui::input-group.addon align="inline-end">
            <x-ui::input-group.button size="icon-xs" :disabled="$attributes->get('disabled', false)" x-on:click="toggle()" x-bind:aria-pressed="revealed"
                aria-label="{{ __('ui::ui.password_visibility') }}"><x-ui::icon name="eye" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
</div>
