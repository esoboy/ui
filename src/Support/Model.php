<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;
use Livewire\Component;

/**
 * What a control's wire:model is bound to.
 */
final class Model
{
    public static function checked(ComponentAttributeBag $attributes, bool $fallback = false, mixed $option = null): bool
    {
        $value = self::value($attributes, $fallback);
        if ($option === null) {
            return (bool) $value;
        }
        if (is_array($value)) {
            return in_array((string) $option, array_map('strval', $value), true);
        }

        return self::key($attributes) !== null ? $value !== null && (string) $value === (string) $option : $fallback;
    }

    /**
     * The small bridge an Alpine interaction needs to read the existing Livewire
     * property. It never owns a second copy of that property's value.
     *
     * @return array{property: ?string, live: bool, blur: bool}
     */
    public static function binding(ComponentAttributeBag $attributes): array
    {
        $directive = collect(array_keys($attributes->getAttributes()))
            ->first(fn ($name) => is_string($name) && str_starts_with($name, 'wire:model'));
        $modifiers = explode('.', is_string($directive) ? $directive : '');

        return [
            'property' => self::component() ? self::key($attributes) : null,
            'live' => count(array_intersect(['live', 'change', 'lazy', 'blur'], $modifiers)) > 0,
            'blur' => in_array('blur', $modifiers, true),
        ];
    }

    public static function inherited(?string $binding, mixed $value = null): mixed
    {
        return self::value(OverlayModel::attributes($binding) ?? new ComponentAttributeBag, $value);
    }

    public static function input(ComponentAttributeBag $attributes, ?int $index = null, bool $number = false): ComponentAttributeBag
    {
        $binding = [];

        foreach ($attributes->getAttributes() as $name => $value) {
            if (str_starts_with($name, 'wire:model')) {
                if ($number) {
                    // Livewire 4 applies input coercion from the modifiers before .live.
                    $modifiers = array_diff(array_slice(explode('.', $name), 1), ['number']);
                    $name = implode('.', ['wire:model', 'number', ...$modifiers]);
                }
                $binding[$name] = $value.($index === null ? '' : '.'.$index);
            }
        }

        return new ComponentAttributeBag($binding);
    }

    public static function scope(string $prefix, mixed $loop = null): string
    {
        $path = [];
        while ($loop !== null) {
            array_unshift($path, $loop->index);
            $loop = $loop->parent;
        }

        $owner = self::component()?->getId() ?? 'page';
        $base = $prefix.'-'.$owner.($path ? '-'.implode('-', $path) : '');

        return $base.'-'.Sequence::next('scope:'.$owner.':'.$base);
    }

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
