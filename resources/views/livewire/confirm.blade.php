<div class="contents" data-ui-confirm-host>
    <x-ui::alert-dialog name="ui-confirm">
        <x-ui::alert-dialog.content>
            <x-ui::alert-dialog.header>
                <x-ui::alert-dialog.title>{{ $panel === 'ui-confirm' && $question !== '' ? $question : __('ui::confirm.title') }}</x-ui::alert-dialog.title>
                @if ($panel === 'ui-confirm' && $description !== '')
                    <x-ui::alert-dialog.description>{{ $description }}</x-ui::alert-dialog.description>
                @endif
            </x-ui::alert-dialog.header>
            <x-ui::alert-dialog.footer>
                <x-ui::alert-dialog.cancel><x-ui::button variant="outline">{{ __('ui::confirm.cancel') }}</x-ui::button></x-ui::alert-dialog.cancel>
                <x-ui::alert-dialog.action><x-ui::button value="confirm">{{ $panel === 'ui-confirm' && $label !== '' ? $label : __('ui::confirm.continue') }}</x-ui::button></x-ui::alert-dialog.action>
            </x-ui::alert-dialog.footer>
        </x-ui::alert-dialog.content>
    </x-ui::alert-dialog>

    <x-ui::alert-dialog name="ui-confirm-destructive">
        <x-ui::alert-dialog.content size="sm">
            <x-ui::alert-dialog.header>
                <x-ui::alert-dialog.media class="bg-destructive/10 text-destructive dark:bg-destructive/20"><x-ui::icon name="trash" /></x-ui::alert-dialog.media>
                <x-ui::alert-dialog.title>{{ $panel === 'ui-confirm-destructive' && $question !== '' ? $question : __('ui::confirm.title') }}</x-ui::alert-dialog.title>
                @if ($panel === 'ui-confirm-destructive' && $description !== '')
                    <x-ui::alert-dialog.description>{{ $description }}</x-ui::alert-dialog.description>
                @endif
            </x-ui::alert-dialog.header>
            <x-ui::alert-dialog.footer>
                <x-ui::alert-dialog.cancel><x-ui::button variant="outline">{{ __('ui::confirm.cancel') }}</x-ui::button></x-ui::alert-dialog.cancel>
                <x-ui::alert-dialog.action><x-ui::button variant="destructive" value="confirm">{{ $panel === 'ui-confirm-destructive' && $label !== '' ? $label : __('ui::confirm.delete') }}</x-ui::button></x-ui::alert-dialog.action>
            </x-ui::alert-dialog.footer>
        </x-ui::alert-dialog.content>
    </x-ui::alert-dialog>

    @foreach (['ui-confirm-popover' => 'default', 'ui-confirm-popover-destructive' => 'destructive'] as $id => $variant)
    @php($variantLabel = $variant === 'destructive' ? 'delete' : 'continue')
    <x-ui::popover.content :id="$id" side="top" class="w-auto max-w-72">
        <x-ui::popover.header>
            <x-ui::popover.title>{{ $panel === $id && $question !== '' ? $question : __('ui::confirm.title') }}</x-ui::popover.title>
            @if ($panel === $id && $description !== '')
                <x-ui::popover.description>{{ $description }}</x-ui::popover.description>
            @endif
        </x-ui::popover.header>
        <div class="flex justify-end gap-2">
            <x-ui::button variant="outline" size="sm" :commandfor="$id" command="hide-popover" autofocus>{{ __('ui::confirm.cancel') }}</x-ui::button>
            <x-ui::button :variant="$variant" size="sm" :commandfor="$id" command="hide-popover" value="confirm">{{ $panel === $id && $label !== '' ? $label : __('ui::confirm.'.$variantLabel) }}</x-ui::button>
        </div>
    </x-ui::popover.content>
    @endforeach
</div>
