<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Esoboy\Ui\Facades\Ui;
use Livewire\Component;

class Notifier extends Component
{
    public function saved(): void
    {
        Ui::toast('Saved');
    }

    public function failed(): void
    {
        Ui::toast('Failed', description: 'Try again.', type: 'error', duration: 8000);
    }

    public function deleted(): void
    {
        Ui::toast('Deleted', type: 'error', action: ['label' => 'Undo', 'method' => 'restore', 'params' => [7]], id: 'delete-7');
    }

    public function restore(int $id): void {}

    public function edit(): void
    {
        Ui::dialog('edit')->open();
    }

    public function done(): void
    {
        Ui::dialog('edit')->close();
    }

    public function welcome(): void
    {
        Ui::tour('onboarding')->start();
    }

    public function resume(): void
    {
        Ui::tour('onboarding')->start(2);
    }

    public function skip(): void
    {
        Ui::tour('onboarding')->close();
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
