<div class="ui-icon-select-pagination" x-on:click="if ($event.target.closest('button')) browseChanged = true">
    <x-ui::button variant="ghost" size="icon-xs" tabindex="-1" x-on:mousedown.prevent
        wire:click="showResultPage({{ $resultPage - 1 }})" wire:loading.attr="disabled" :disabled="$resultPage === 1"
        aria-keyshortcuts="PageUp" :aria-label="__('ui::ui.previous_page')" :title="__('ui::ui.previous_page')">
        <x-ui::icon name="chevron-left" />
    </x-ui::button>
    <span aria-live="polite" aria-atomic="true">{{ __('ui::ui.page', ['page' => $resultPage, 'total' => $this->lastResultPage]) }}</span>
    <x-ui::button variant="ghost" size="icon-xs" tabindex="-1" x-on:mousedown.prevent
        wire:click="showResultPage({{ $resultPage + 1 }})" wire:loading.attr="disabled" :disabled="$resultPage === $this->lastResultPage"
        aria-keyshortcuts="PageDown" :aria-label="__('ui::ui.next_page')" :title="__('ui::ui.next_page')">
        <x-ui::icon name="chevron-right" />
    </x-ui::button>
</div>
