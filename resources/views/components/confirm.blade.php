@blaze

{{-- Place once in the layout: wire:confirm then asks with these instead of the browser's confirm(). .destructive asks as shadcn's destructive alert dialog, with a red confirm button; .popover asks in a popover at the element; "Question?|Label" names the confirm button. --}}
<x-ui::alert-dialog name="ui-confirm">
    <x-ui::alert-dialog.content>
        <x-ui::alert-dialog.header>
            <x-ui::alert-dialog.title>{{ __('ui::confirm.title') }}</x-ui::alert-dialog.title>
        </x-ui::alert-dialog.header>
        <x-ui::alert-dialog.footer>
            <x-ui::alert-dialog.cancel>{{ __('ui::confirm.cancel') }}</x-ui::alert-dialog.cancel>
            <x-ui::alert-dialog.action value="confirm">{{ __('ui::confirm.continue') }}</x-ui::alert-dialog.action>
        </x-ui::alert-dialog.footer>
    </x-ui::alert-dialog.content>
</x-ui::alert-dialog>

<x-ui::alert-dialog name="ui-confirm-destructive">
    <x-ui::alert-dialog.content size="sm">
        <x-ui::alert-dialog.header>
            <x-ui::alert-dialog.media class="bg-destructive/10 text-destructive dark:bg-destructive/20"><x-ui::icon name="trash" /></x-ui::alert-dialog.media>
            <x-ui::alert-dialog.title>{{ __('ui::confirm.title') }}</x-ui::alert-dialog.title>
        </x-ui::alert-dialog.header>
        <x-ui::alert-dialog.footer>
            <x-ui::alert-dialog.cancel>{{ __('ui::confirm.cancel') }}</x-ui::alert-dialog.cancel>
            <x-ui::alert-dialog.action variant="destructive" value="confirm">{{ __('ui::confirm.delete') }}</x-ui::alert-dialog.action>
        </x-ui::alert-dialog.footer>
    </x-ui::alert-dialog.content>
</x-ui::alert-dialog>

@foreach (['ui-confirm-popover' => 'default', 'ui-confirm-popover-destructive' => 'destructive'] as $id => $variant)
<x-ui::popover.content :id="$id" side="top" class="w-auto max-w-72">
    <x-ui::popover.header>
        <x-ui::popover.title>{{ __('ui::confirm.title') }}</x-ui::popover.title>
    </x-ui::popover.header>
    <div class="flex justify-end gap-2">
        <x-ui::button variant="outline" size="sm" :commandfor="$id" command="hide-popover" autofocus>{{ __('ui::confirm.cancel') }}</x-ui::button>
        <x-ui::button :variant="$variant" size="sm" :commandfor="$id" command="hide-popover" value="confirm">{{ $variant === 'destructive' ? __('ui::confirm.delete') : __('ui::confirm.continue') }}</x-ui::button>
    </div>
</x-ui::popover.content>
@endforeach
