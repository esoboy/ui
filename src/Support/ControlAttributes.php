<?php

namespace Esoboy\Ui\Support;

use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Livewire\Livewire;
use WeakMap;

/** The public input contract; no HTML parsing or framework lifecycle hooks. */
final readonly class ControlAttributes
{
    public string $containerTag;

    /** @param array<string, mixed> $parameters */
    private function __construct(public string $key, public ?string $model, public array $parameters, public ComponentAttributeBag $loading)
    {
        $this->containerTag = $loading->isEmpty() ? 'div' : 'fieldset';
    }

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

        $identity = $attributes->get('wire:key') ?? $attributes->get('id');
        $mode = $model !== null ? 'bound' : ($controlled ? 'controlled' : 'internal');
        $identity ??= self::instanceName($component);
        $fieldAttributes = $attributes->except(['wire:key', 'value', 'initial-value', 'disabled', 'readonly'])
            ->whereDoesntStartWith('wire:model')->merge(['id' => self::modelId($model)])->all();

        return new self(
            $component.':'.hash('xxh128', serialize([$identity, $mode, array_key_first($attributes->whereStartsWith('wire:model')->all()), $model])),
            $model,
            [
                'disabled' => false,
                'readonly' => false,
                'id' => $attributes->get('id') ?? self::modelId($model),
                ...$attributes->except(['wire:key', 'value', 'initial-value'])->all(),
                'applicationLocale' => app()->getLocale(),
                'fieldAttributes' => $fieldAttributes,
                'mode' => $mode,
                'externalValue' => $controlled ? $attributes->get('value') : null,
                'initialValue' => $initial ? $attributes->get('initial-value') : null,
            ],
            self::loading($attributes),
        );
    }

    public static function groupBinding(ComponentAttributeBag $item, ?ComponentAttributeBag $group): ComponentAttributeBag
    {
        $binding = $item->whereStartsWith('wire:model');

        return $binding->isEmpty() && $group ? $group->whereStartsWith('wire:model') : $binding;
    }

    public static function checked(ComponentAttributeBag $binding, mixed $value, bool $default): bool
    {
        $model = $binding->first();
        if ($model === null || ! ($owner = Livewire::current())) {
            return $default;
        }
        $selection = data_get($owner, $model);

        return in_array((string) $value, array_map(strval(...), (array) $selection), true);
    }

    public static function loading(ComponentAttributeBag $attributes): ComponentAttributeBag
    {
        $directives = $attributes->filter(static fn ($value, $key) => str_starts_with($key, 'wire:loading.') && in_array('attr', explode('.', $key), true) && $value === 'disabled');

        return $directives->isEmpty() ? $directives : $directives->merge($attributes->whereStartsWith('wire:target')->all());
    }

    public static function instanceName(string $component): string
    {
        $parent = Livewire::current();
        if (! $parent) {
            return $component.'-'.Str::uuid();
        }
        // Distinguish anonymous siblings while keeping their names stable across parent renders.
        static $positions;
        $positions ??= new WeakMap;
        $counts = $positions[$parent] ?? [];
        $index = $counts[$component] ?? 0;
        $counts[$component] = $index + 1;
        $positions[$parent] = $counts;

        return $parent->getId().'-'.$component.'-'.$index;
    }

    public static function id(ComponentAttributeBag $attributes, bool $choice = false): ?string
    {
        $base = self::modelId($attributes->whereStartsWith('wire:model')->first());
        if ($choice && $base !== null && $attributes->has('value')) {
            $base .= ':'.rawurlencode((string) $attributes->get('value'));
        }

        return $attributes->get('id') ?? $base;
    }

    private static function modelId(?string $model): ?string
    {
        return $model === null ? null : 'ui-'.str_replace(['-', '.'], ['%2D', '-'], rawurlencode($model));
    }

    /** @param list<string> $names Native attributes accepted by this type of input. */
    public static function forInput(ComponentAttributeBag $attributes, array $names): ComponentAttributeBag
    {
        return $attributes->filter(static fn ($value, string $name): bool => str_starts_with($name, 'aria-') || str_starts_with($name, 'wire:loading') || str_starts_with($name, 'wire:target') || in_array($name, $names, true));
    }
}
