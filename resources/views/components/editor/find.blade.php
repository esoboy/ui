@blaze

@props(['count' => ''])

{{-- The find and replace bar, under the toolbar: Enter finds the next match (Shift the previous), Escape closes. --}}
<div data-slot="editor-find" role="search" aria-label="{{ __('ui::editor.find-replace') }}" hidden wire:ignore.self>
    <x-ui::input-group>
        <x-ui::input-group.input type="search" data-editor-find-input :placeholder="__('ui::editor.find-placeholder')" :aria-label="__('ui::editor.find-placeholder')" wire:ignore.self />
        <x-ui::input-group.addon align="inline-end">
            <span data-editor-find-count aria-live="polite">{{ $count }}</span>
            <x-ui::editor.tool size="default" data-input-group-size="icon-xs" data-editor-action="find-case" aria-pressed="false" :aria-label="__('ui::editor.match-case')" wire:ignore.self><x-ui::icon name="case-sensitive" /></x-ui::editor.tool>
        </x-ui::input-group.addon>
    </x-ui::input-group>
    <x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-action="find-previous" :aria-label="__('ui::editor.previous-match')" wire:ignore.self><x-ui::icon name="chevron-up" /></x-ui::editor.tool>
    <x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-action="find-next" :aria-label="__('ui::editor.next-match')" wire:ignore.self><x-ui::icon name="chevron-down" /></x-ui::editor.tool>
    <x-ui::input data-editor-replace-input :placeholder="__('ui::editor.replace-placeholder')" :aria-label="__('ui::editor.replace-placeholder')" wire:ignore.self />
    <x-ui::button variant="outline" size="sm" data-editor-action="replace" wire:ignore.self>{{ __('ui::editor.replace') }}</x-ui::button>
    <x-ui::button variant="outline" size="sm" data-editor-action="replace-all" wire:ignore.self>{{ __('ui::editor.replace-all') }}</x-ui::button>
    <x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-action="find-close" :aria-label="__('ui::editor.close-find')"><x-ui::icon name="x" /></x-ui::editor.tool>
</div>
