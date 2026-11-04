@blaze

{{-- The find and replace bar, under the toolbar: Enter finds the next match (Shift the previous), Escape closes. --}}
<div data-slot="editor-find" role="search" aria-label="{{ __('ui::editor.find-replace') }}" hidden wire:ignore>
    <x-ui::input-group>
        <x-ui::input-group.input type="search" data-editor-find-input :placeholder="__('ui::editor.find-placeholder')" :aria-label="__('ui::editor.find-placeholder')" />
        <x-ui::input-group.addon align="inline-end">
            <span data-editor-find-count aria-live="polite"></span>
            <x-ui::input-group.button size="icon-xs" data-editor-action="find-case" aria-pressed="false" :aria-label="__('ui::editor.match-case')"><x-ui::icon name="case-sensitive" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
    <x-ui::button variant="ghost" size="icon-sm" data-editor-action="find-previous" :aria-label="__('ui::editor.previous-match')"><x-ui::icon name="chevron-up" /></x-ui::button>
    <x-ui::button variant="ghost" size="icon-sm" data-editor-action="find-next" :aria-label="__('ui::editor.next-match')"><x-ui::icon name="chevron-down" /></x-ui::button>
    <x-ui::input data-editor-replace-input :placeholder="__('ui::editor.replace-placeholder')" :aria-label="__('ui::editor.replace-placeholder')" />
    <x-ui::button variant="outline" size="sm" data-editor-action="replace">{{ __('ui::editor.replace') }}</x-ui::button>
    <x-ui::button variant="outline" size="sm" data-editor-action="replace-all">{{ __('ui::editor.replace-all') }}</x-ui::button>
    <x-ui::button variant="ghost" size="icon-sm" data-editor-action="find-close" :aria-label="__('ui::editor.close-find')"><x-ui::icon name="x" /></x-ui::button>
</div>
