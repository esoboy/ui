<?php

use Esoboy\Ui\Facades\Ui;
use Esoboy\Ui\Tests\Fixtures\Notifier;
use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

it('dispatches a toast from a Livewire component', function () {
    Livewire::test(Notifier::class)
        ->call('saved')
        ->assertDispatched('ui-toast', message: 'Saved', description: null, type: 'default', duration: null, action: null, id: null);
});

it('passes toast options through', function () {
    Livewire::test(Notifier::class)
        ->call('failed')
        ->assertDispatched('ui-toast', message: 'Failed', description: 'Try again.', type: 'error', duration: 8000, action: null, id: null);
});

it('sends an action that calls back the component, and an id to update the toast by', function () {
    $component = Livewire::test(Notifier::class);

    $component->call('deleted')->assertDispatched('ui-toast', fn (string $event, array $params) => $params['id'] === 'delete-7'
        && $params['action'] === ['component' => $component->id(), 'params' => [7], 'label' => 'Undo', 'method' => 'restore']);
});

it('opens and closes a dialog by name', function () {
    Livewire::test(Notifier::class)
        ->call('edit')
        ->assertDispatched('ui-dialog', name: 'edit', open: true)
        ->call('done')
        ->assertDispatched('ui-dialog', name: 'edit', open: false);
});

it('reaches the browser from a Livewire form object', function () {
    Livewire::test(new class extends Component
    {
        public NotifyingForm $form;

        public function save(): void
        {
            $this->form->save();
        }

        public function render(): string
        {
            return '<div></div>';
        }
    })
        ->call('save')
        ->assertDispatched('ui-toast', message: 'Saved from the form');
});

it('says so when called outside a Livewire request', function () {
    Ui::toast('Nobody hears this');
})->throws(LogicException::class, 'Ui::toast() reaches the browser through Livewire');

class NotifyingForm extends Form
{
    public function save(): void
    {
        Ui::toast('Saved from the form');
    }
}

it('renders the toaster as the toast viewport, with what toasts are cloned from', function () {
    $html = (string) $this->blade('<x-ui::toaster />');

    expect($html)
        ->toContain('<div x-persist="ui-toaster">')
        ->toContain('<div role="region" aria-live="polite" aria-atomic="false" aria-relevant="additions text" aria-label="Notifications" tabindex="-1" popover="manual" data-slot="toast-viewport" data-limit="3" data-timeout="5000" wire:ignore>')
        ->toContain('<template data-template="toast">')
        ->toMatch('/<div data-slot="toast" aria-modal="false" tabindex="0">\s*<div data-slot="toast-content">\s*<span data-slot="toast-icon"><\/span>/')
        ->toMatch('/data-variant="outline" data-size="sm" data-toast-action="data-toast-action"><\/button>/')
        ->toMatch('/data-variant="ghost" data-size="icon-sm" data-toast-close="data-toast-close" aria-label="Close toast"><svg[^>]*><path d="M18 6 6 18"\/>/')
        ->toContain('<template data-template="icon-error"><svg')
        ->toMatch('/<template data-template="icon-loading"><svg[^>]*data-spin/');

    $this->blade('<x-ui::toaster :limit="5" :timeout="8000" />')
        ->assertSee('data-limit="5" data-timeout="8000"', false);
});

it('renders the confirm dialog with translated labels', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::confirm />')
        ->assertSee('<dialog id="ui-confirm" role="alertdialog" data-slot="alert-dialog-content"', false)
        ->assertSee('<h2 id="ui-confirm-title" data-slot="alert-dialog-title">确定要继续吗？</h2>', false)
        ->assertSee('commandfor="ui-confirm" command="close">取消</button>', false)
        ->assertSee('commandfor="ui-confirm" command="close" value="confirm">继续</button>', false);
});

it('renders a destructive confirm dialog and confirm popovers', function () {
    $html = (string) $this->blade('<x-ui::confirm />');

    expect($html)
        ->toContain('<dialog id="ui-confirm-destructive" role="alertdialog" data-slot="alert-dialog-content" data-size="sm"')
        ->toMatch('/data-slot="alert-dialog-media"[^>]*><svg/')
        ->toMatch('/data-variant="destructive"[^>]*commandfor="ui-confirm-destructive" command="close" value="confirm">Delete<\/button>/')
        ->toMatch('/<div popover="auto" data-slot="popover-content" data-side="top"[^>]*id="ui-confirm-popover">/')
        ->toMatch('/commandfor="ui-confirm-popover" command="hide-popover" autofocus="autofocus">Cancel<\/button>/')
        ->toMatch('/commandfor="ui-confirm-popover" command="hide-popover" value="confirm">Continue<\/button>/')
        ->toMatch('/data-variant="destructive"[^>]*commandfor="ui-confirm-popover-destructive" command="hide-popover" value="confirm">Delete<\/button>/');
});
