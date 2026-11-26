<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/** @property-read mixed $current */
abstract class ValueControl extends Component
{
    use ForwardsControlAttributes;

    #[Locked]
    public ?string $modelPath = null;

    #[Locked]
    public ?string $modelDirective = null;

    #[Modelable]
    public mixed $value = null;

    #[Locked]
    public string $mode = 'internal';

    #[Locked]
    public mixed $initialFormValue = null;

    #[Reactive]
    public mixed $externalValue = null;

    #[Reactive]
    public bool $disabled = false;

    #[Reactive]
    public bool $readonly = false;

    #[Reactive]
    public string $applicationLocale = '';

    public function updatedApplicationLocale(string $locale): void
    {
        app()->setLocale($locale);
    }

    public function mount(mixed $initialValue = null): void
    {
        if ($this->mode === 'internal') {
            $this->value = $initialValue;
        }
        $this->initialFormValue = $this->current;
    }

    /** @return list<string|int>|null */
    public function multipleChoices(): ?array
    {
        return null;
    }

    public function fieldId(): string
    {
        return 'ui-field-'.$this->getId();
    }

    /** Attach a Blade-computed candidate to an actual field edit. */
    public function fieldEdit(mixed $next): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'data-ui-edit' => $this->fieldId(),
            'data-ui-next' => e(json_encode($next, JSON_THROW_ON_ERROR)),
        ]);
    }

    public function fieldBinding(?string $member = null): ComponentAttributeBag
    {
        $path = $this->modelPath !== null ? '$parent.'.$this->modelPath : 'value';

        return new ComponentAttributeBag([
            $this->modelDirective ?? 'wire:model.live' => $member === null ? $path : $path.'.'.$member,
        ]);
    }

    public function updatedValue(mixed $value): void
    {
        if (! $this->disabled && ! $this->readonly && $this->mode !== 'controlled') {
            unset($this->current);
            $this->dispatch('value-changed', value: $this->current);
        }
    }

    public function resetFormValue(): void
    {
        $this->applyValue($this->initialFormValue);
    }

    #[Computed]
    public function current(): mixed
    {
        return $this->mode === 'controlled' ? $this->externalValue : $this->value;
    }

    protected function changeValue(mixed $value): void
    {
        if ($this->disabled || $this->readonly) {
            return;
        }

        $this->applyValue($value);
    }

    protected function applyValue(mixed $value): void
    {
        if ($this->mode !== 'controlled') {
            $this->value = $value;
        }

        unset($this->current);
        $this->dispatch('value-changed', value: $value);
    }

    abstract public function render(): View;
}
