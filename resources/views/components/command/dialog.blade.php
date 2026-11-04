@blaze

@props([
    'name',
    'title' => null,
    'description' => null,
    'showCloseButton' => false,
])

<x-ui::dialog :name="$name">
    <x-ui::dialog.content :show-close-button="$showCloseButton" :attributes="$attributes->merge(['data-command-dialog' => true])">
        <x-ui::dialog.header class="sr-only">
            <x-ui::dialog.title>{{ $title ?? __('ui::command.title') }}</x-ui::dialog.title>
            <x-ui::dialog.description>{{ $description ?? __('ui::command.description') }}</x-ui::dialog.description>
        </x-ui::dialog.header>
        <x-ui::command>{{ $slot }}</x-ui::command>
    </x-ui::dialog.content>
</x-ui::dialog>
