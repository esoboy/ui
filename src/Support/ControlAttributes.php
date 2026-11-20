<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Livewire\Livewire;

/** The public input contract; no HTML parsing or framework lifecycle hooks. */
final readonly class ControlAttributes
{
    /** @param array<string, mixed> $parameters */
    private function __construct(public ?string $key, public ?string $model, public array $parameters) {}

    /** @param array<string, mixed> $props Raw Blade props, before HTML attribute escaping. */
    public static function from(ComponentAttributeBag $attributes, string $component, array $props = []): self
    {
        foreach (['value' => 'value', 'initialValue' => 'initial-value'] as $prop => $attribute) {
            if (array_key_exists($prop, $props)) {
                $attributes = $attributes->merge([$attribute => $props[$prop]], escape: false);
            }
        }
        $model = $attributes->whereStartsWith('wire:model')->first();
        $controlled = $attributes->has('value');
        $initial = $attributes->has('initial-value');

        if (($model !== null && $controlled) || (($model !== null || $controlled) && $initial)) {
            throw new InvalidArgumentException(__('ui::errors.binding_sources'));
        }

        $identity = $attributes->get('wire:key') ?? $attributes->get('id') ?? $model ?? $attributes->get('name');
        if ($identity === null && Livewire::current()) {
            throw new InvalidArgumentException(__('ui::errors.binding_identity', ['component' => $component]));
        }

        return new self(
            $identity === null ? null : $component.':'.$identity,
            $model,
            [
                'disabled' => false,
                'readonly' => false,
                'id' => $attributes->get('id') ?? self::modelId($model),
                ...$attributes->except(['wire:key', 'value', 'initial-value'])->all(),
                'applicationLocale' => app()->getLocale(),
                'mode' => $model !== null ? 'bound' : ($controlled ? 'controlled' : 'internal'),
                'externalValue' => $controlled ? $attributes->get('value') : null,
                'initialValue' => $initial ? $attributes->get('initial-value') : null,
            ],
        );
    }

    public static function id(ComponentAttributeBag $attributes): ?string
    {
        return $attributes->get('id') ?? self::modelId($attributes->whereStartsWith('wire:model')->first());
    }

    private static function modelId(?string $model): ?string
    {
        return $model === null ? null : 'ui-'.str_replace(['.', '_'], '-', $model);
    }

    /** @param list<string> $names Native attributes accepted by this type of input. */
    public static function forInput(ComponentAttributeBag $attributes, array $names): ComponentAttributeBag
    {
        return $attributes->filter(static fn ($value, string $name): bool => str_starts_with($name, 'aria-') || in_array($name, $names, true));
    }
}
