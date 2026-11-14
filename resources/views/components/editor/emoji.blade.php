@blaze

{{-- The Emoji Picker in a popover: the emoji picked goes in at the cursor. --}}
<x-ui::popover>
    <x-ui::popover.trigger><x-ui::editor.tool variant="ghost" size="icon-sm" data-editor-opens="emoji" :aria-label="__('ui::editor.emoji')" :attributes="$attributes"><x-ui::icon name="face-slightly-smiling" /></x-ui::editor.tool></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-emoji-content>
        <x-ui::emoji-picker class="h-[342px]" footer />
    </x-ui::popover.content>
</x-ui::popover>
