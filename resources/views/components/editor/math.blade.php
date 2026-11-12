@blaze

{{-- The formula button: its popover writes a new one at the cursor. --}}
<x-ui::popover>
    <x-ui::popover.trigger><x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-opens="math" :aria-label="__('ui::editor.math')" :attributes="$attributes"><x-ui::icon name="radical" /></x-ui::editor.tool></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-math-content>
        <x-ui::editor.math-form />
    </x-ui::popover.content>
</x-ui::popover>
