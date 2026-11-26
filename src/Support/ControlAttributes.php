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
    private function __construct(public string $key, public ?string $model, public array $parameters, public ComponentAttributeBag $loading, public ComponentAttributeBag $directives)
    {
        $this->containerTag = $loading->isEmpty() ? 'div' : 'fieldset';
    }

    /** @param array<string, mixed> $props Raw Blade props, before HTML attribute escaping. */
    public static function from(ComponentAttributeBag $attributes, string $component, array $props = []): self
    {
        foreach (['value' => 'value', 'initialValue' => 'initial-value'] as $prop => $attribute) {
            if (array_key_exists($prop, $props) || array_key_exists($attribute, $props)) {
                $attributes = $attributes->merge([$attribute => array_key_exists($prop, $props) ? $props[$prop] : $props[$attribute]], escape: false);
            }
        }
        $models = $attributes->whereStartsWith('wire:model');
        $directive = array_key_first($models->all());
        $model = self::modelPath($models->first());
        $controlled = $attributes->has('value');
        $initial = $attributes->has('initial-value');

        if (($model !== null && $controlled) || (($model !== null || $controlled) && $initial)) {
            throw new InvalidArgumentException(__('ui::errors.binding_sources'));
        }

        $identity = $attributes->get('wire:key') ?? $attributes->get('id');
        $mode = $model !== null ? 'bound' : ($controlled ? 'controlled' : 'internal');
        $identity ??= self::instanceName($component, $attributes->get('ui-site')).':'.$attributes->get('name');
        $attributes = $attributes->except('ui-site');
        $directives = $attributes->whereStartsWith('wire:')->whereDoesntStartWith('wire:model')->except('wire:key');
        $fieldAttributes = $attributes->except(['wire:key', 'value', 'initial-value', 'disabled', 'readonly'])
            ->whereDoesntStartWith('wire:')->merge(['id' => self::modelId($model)])->all();

        return new self(
            $component.':'.hash('xxh128', serialize([$identity, $mode, array_key_first($attributes->whereStartsWith('wire:model')->all()), $model])),
            $model,
            [
                'disabled' => false,
                'readonly' => false,
                'id' => $attributes->get('id') ?? self::modelId($model),
                ...$attributes->except(['wire:key', 'value', 'initial-value', ...array_keys($models->all()), ...array_keys($directives->all())])->all(),
                ...($model !== null ? [$directive => $model, 'modelPath' => $model, 'modelDirective' => $directive] : []),
                'applicationLocale' => app()->getLocale(),
                'fieldAttributes' => $fieldAttributes,
                'mode' => $mode,
                'externalValue' => $controlled ? $attributes->get('value') : null,
                'initialValue' => $initial ? $attributes->get('initial-value') : null,
            ],
            self::loading($attributes),
            $directives,
        );
    }

    public static function modelPath(?string $path): ?string
    {
        return $path === null ? null : ltrim(preg_replace('/\[([\'"]?)(.+?)\1\]/', '.$2', $path) ?? $path, '.');
    }

    public static function formReset(ComponentAttributeBag $attributes, string $action = 'resetFormValue'): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'data-ui-form' => $attributes->get('form'),
            'x-on:reset.window' => "if (\$event.target === (\$el.dataset.uiForm ? document.getElementById(\$el.dataset.uiForm) : \$el.closest('form'))) \$nextTick(() => { if (!\$event.defaultPrevented && \$el.isConnected) \$wire.{$action}() })",
        ]);
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
        $selection = data_get($owner, self::modelPath($model));

        return in_array((string) $value, array_map(strval(...), (array) $selection), true);
    }

    public static function loading(ComponentAttributeBag $attributes): ComponentAttributeBag
    {
        $directives = $attributes->filter(static fn ($value, $key) => str_starts_with($key, 'wire:loading.') && in_array('attr', explode('.', $key), true) && $value === 'disabled');

        return $directives->isEmpty() ? $directives : $directives->merge($attributes->whereStartsWith('wire:target')->all());
    }

    public static function instanceName(string $component, ?string $site = null): string
    {
        $parent = Livewire::current();
        if (! $parent) {
            return $component.'-'.Str::uuid();
        }
        $component .= '-'.$site;
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
