<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Livewire\Livewire;

/** Render-time contract for controls that do not need a Livewire child. */
final readonly class NativeControl
{
    public string $containerTag;

    /** @param array<string, mixed> $config */
    private function __construct(public string $key, public ?string $id, public array $config, public ComponentAttributeBag $loading)
    {
        $this->containerTag = $loading->isEmpty() ? 'div' : 'fieldset';
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $options
     */
    public static function from(ComponentAttributeBag $attributes, string $component, array $props, array $options): self
    {
        $binding = ControlAttributes::from($attributes, $component, $props);
        $parameters = $binding->parameters;
        $value = match ($parameters['mode']) {
            'bound' => data_get(Livewire::current(), $binding->model),
            'controlled' => $parameters['externalValue'],
            default => $parameters['initialValue'],
        };
        $config = [...$options, 'mode' => $parameters['mode'], 'model' => $binding->model, 'value' => $value,
            'disabled' => (bool) $parameters['disabled'], 'readonly' => (bool) $parameters['readonly']];
        self::validate($component, $config);

        return new self($binding->key, $parameters['id'], $config, $binding->loading);
    }

    public static function numericDirective(string $directive): string
    {
        $parts = array_filter(explode('.', $directive), static fn ($part) => $part !== 'number');

        return implode('.', ['wire:model', 'number', ...array_slice($parts, 1)]);
    }

    public function inputId(int $index): ?string
    {
        if ($this->id === null || $this->id === '') {
            return null;
        }

        return $index === 0 ? $this->id : $this->id.'-'.$index;
    }

    /** @param array<string, mixed> $config */
    private static function validate(string $component, array $config): void
    {
        $value = $config['value'];
        if (in_array($component, ['number-input', 'slider'], true)) {
            foreach (['min', 'max', 'step'] as $option) {
                if ($config[$option] !== null && (! is_numeric($config[$option]) || ! is_finite((float) $config[$option]))) {
                    throw new InvalidArgumentException(__('ui::errors.numeric_bounds'));
                }
            }
            if ($config['step'] <= 0 || ($config['min'] !== null && $config['max'] !== null && $config['max'] < $config['min'])) {
                throw new InvalidArgumentException(__('ui::errors.numeric_step'));
            }
            $values = $component === 'slider' && is_array($value) ? $value : [$value];
            if (! array_is_list($values) || $values === [] || count($values) > 20) {
                throw new InvalidArgumentException(__('ui::errors.slider_values'));
            }
            foreach ($values as $number) {
                if ($number !== null && (! is_numeric($number) || ! is_finite((float) $number) || ($config['min'] !== null && $number < $config['min']) || ($config['max'] !== null && $number > $config['max']))) {
                    throw new InvalidArgumentException(__('ui::errors.numeric_value'));
                }
            }
            if ($component === 'slider' && ($config['min'] === null || $config['max'] === null || $config['max'] <= $config['min'] || ! in_array($config['orientation'], ['horizontal', 'vertical'], true) || (is_array($value) && in_array(null, $value, true)))) {
                throw new InvalidArgumentException(__('ui::errors.slider_options'));
            }
        } elseif ($component === 'input-otp') {
            $positions = is_array($config['separatorAfter']) ? $config['separatorAfter'] : ($config['separatorAfter'] === 0 ? [] : [$config['separatorAfter']]);
            $validPositions = array_is_list($positions);
            $previous = 0;
            foreach ($positions as $position) {
                $validPositions = $validPositions && is_int($position) && $position > $previous && $position < $config['length'];
                $previous = $position;
            }
            if ($config['length'] < 1 || $config['length'] > 12 || ! in_array($config['pattern'], ['numeric', 'alphanumeric'], true) || ! $validPositions) {
                throw new InvalidArgumentException(__('ui::errors.otp_options'));
            }
            if ($value !== null && (! is_string($value) || ($config['mode'] !== 'bound' && (strlen($value) > $config['length'] || ! preg_match($config['pattern'] === 'numeric' ? '/^[0-9]*$/' : '/^[a-z0-9]*$/i', $value))))) {
                throw new InvalidArgumentException(__('ui::errors.otp_content'));
            }
        } elseif ($component === 'editor') {
            if (! in_array($config['toolbarPreset'], ['basic', 'full'], true) || ! in_array($config['inputType'], ['auto', 'html', 'markdown'], true) || ! in_array($config['outputType'], ['html', 'markdown'], true)) {
                throw new InvalidArgumentException(__('ui::errors.editor_options'));
            }
            if ($value !== null && (! is_string($value) || strlen($value) > ContentSchema::definition()['maxLength'])) {
                throw new InvalidArgumentException(__('ui::errors.editor_content'));
            }
        }
    }
}
