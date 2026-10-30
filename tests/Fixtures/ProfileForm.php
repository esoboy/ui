<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Livewire\Attributes\Validate;
use Livewire\Component;

class ProfileForm extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|in:starter,pro')]
    public string $plan = '';

    #[Validate('accepted')]
    public bool $terms = false;

    public function save(): void
    {
        $this->validate();
    }

    public function render(): string
    {
        return <<<'BLADE'
            <form wire:submit="save">
                <x-ui::field>
                    <x-ui::field.label for="email">Email</x-ui::field.label>
                    <x-ui::input id="email" wire:model="email" />
                </x-ui::field>

                <x-ui::field.set>
                    <x-ui::radio-group wire:model="plan">
                        <x-ui::radio-group.item value="starter" />
                        <x-ui::radio-group.item value="pro" />
                    </x-ui::radio-group>
                </x-ui::field.set>

                <x-ui::field orientation="horizontal" :show-error="false">
                    <x-ui::checkbox wire:model="terms" />
                </x-ui::field>
            </form>
            BLADE;
    }
}
