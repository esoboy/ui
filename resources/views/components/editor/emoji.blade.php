@blaze

{{-- The Emoji Picker in a popover: the emoji picked goes in at the cursor. --}}
<x-ui::popover>
    <x-ui::popover.trigger variant="ghost" size="icon-sm" data-editor-opens="emoji" :aria-label="__('ui::editor.emoji')" :attributes="$attributes"><x-ui::icon name="face-slightly-smiling" /></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-emoji-content>
        <x-ui::emoji-picker class="h-[342px]">
            <x-ui::emoji-picker.search />
            <x-ui::emoji-picker.content />
            <x-ui::emoji-picker.footer />
        </x-ui::emoji-picker>
    </x-ui::popover.content>
</x-ui::popover>
