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

it('renders and replaces a notification with the same id and ignores a stale dismissal', function () {
    $toaster = Livewire::test('ui.toaster')
        ->call('show', ['id' => 'save', 'message' => 'Saving', 'type' => 'loading'])
        ->assertSee('Saving')
        ->call('show', ['id' => 'save', 'message' => 'Saved <img>', 'type' => 'success'])
        ->assertSee('Saved &lt;img&gt;', false)
        ->assertDontSee('Saving')
        ->call('dismiss', 'save', 1)
        ->assertSee('Saved &lt;img&gt;', false);

    $toaster->call('dismiss', 'save', 2)->assertDontSee('Saved');
});

it('renders a toast viewport without persisting it by default', function () {
    $html = (string) $this->blade('<x-ui::toaster />');

    expect($html)
        ->toContainMarkup('<div role="region" aria-live="polite" aria-atomic="false" aria-relevant="additions text" aria-label="Notifications" tabindex="-1" popover="manual" data-slot="toast-viewport" data-limit="3" data-timeout="5000" wire:ignore.self>')
        ->not->toContain('x-persist=')
        ->not->toContain('data-template=');

    $this->blade('<x-ui::toaster :limit="5" :timeout="8000" />')
        ->assertSee('data-limit="5" data-timeout="8000"', false);
});

it('renders the confirm dialog with translated labels', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::confirm />')
        ->assertSeeMarkup('<dialog id="ui-confirm" role="alertdialog" data-slot="alert-dialog-content">')
        ->assertSeeMarkup('<h2 id="ui-confirm-title" data-slot="alert-dialog-title">确定要继续吗？</h2>')
        ->assertSeeMarkup('commandfor="ui-confirm" command="close">取消</button>')
        ->assertSeeMarkup('value="confirm" commandfor="ui-confirm" command="close">继续</button>');
});

it('renders a request with its own escaped question, label and description', function () {
    Livewire::test('ui.confirm')
        ->call('prepare', 'ui-confirm-popover', 'Publish <draft>?', 'Publish now', 'Subscribers receive this.')
        ->assertSee('Publish &lt;draft&gt;?', false)
        ->assertSee('Publish now')
        ->assertSee('Subscribers receive this.')
        ->call('prepare', 'ui-confirm-popover', '', '', '')
        ->assertDontSee('Publish now')
        ->assertDontSee('Subscribers receive this.');
});

it('renders a destructive confirm dialog and confirm popovers', function () {
    $html = (string) $this->blade('<x-ui::confirm />');

    expect($html)
        ->toContainMarkup('<dialog id="ui-confirm-destructive" role="alertdialog" data-slot="alert-dialog-content" data-size="sm">')
        ->toMatch('/data-slot="alert-dialog-media"[^>]*><svg/')
        ->toMatch('/data-variant="destructive"[^>]*value="confirm" commandfor="ui-confirm-destructive" command="close">\s*Delete\s*<\/button>/')
        ->toContainMarkup('<div id="ui-confirm-popover" popover="auto" data-slot="popover-content" data-side="top">')
        ->toMatch('/commandfor="ui-confirm-popover" command="hide-popover" autofocus="autofocus">Cancel<\/button>/')
        ->toMatch('/commandfor="ui-confirm-popover" command="hide-popover" value="confirm">Continue<\/button>/')
        ->toMatch('/data-variant="destructive"[^>]*commandfor="ui-confirm-popover-destructive" command="hide-popover" value="confirm">Delete<\/button>/');
});
