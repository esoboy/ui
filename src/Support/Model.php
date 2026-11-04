<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;
use Livewire\Component;

/**
 * What a control's wire:model is bound to.
 */
final class Model
{
    /**
     * The current value behind wire:model, read from the Livewire component being
     * rendered, so a control can paint its initial state on the server.
     */
    public static function value(ComponentAttributeBag $attributes, mixed $default = null): mixed
    {
        $component = self::component();
        $key = self::key($attributes);

        if ($component === null || $key === null) {
            return $default;
        }

        return data_get($component, $key, $default);
    }

    /**
     * The Livewire component being rendered or handled, if any.
     */
    public static function component(): ?Component
    {
        return app('livewire')->current() ?: null;
    }

    /**
     * The property wire:model (with any modifiers) points at.
     */
    public static function key(ComponentAttributeBag $attributes): ?string
    {
        foreach ($attributes->getAttributes() as $attribute => $value) {
            if (str_starts_with($attribute, 'wire:model') && is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
