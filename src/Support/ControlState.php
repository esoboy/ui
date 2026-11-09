<?php

namespace Esoboy\Ui\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentAttributeBag;

/**
 * Validation state of a form control, derived from what it is bound to.
 */
final class ControlState
{
    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        public readonly ?string $key,
        public readonly array $messages,
        public readonly ?string $errorId,
        public readonly bool $showError,
    ) {}

    /**
     * Controls only validate inside a field or field set; the nearest one decides whether messages show.
     */
    public static function for(ComponentAttributeBag $attributes, ViewErrorBag|MessageBag|null $errors, mixed $inField = true, mixed $showError = true): self
    {
        $key = self::key($attributes);
        $messages = self::boolean($inField) ? self::messages($errors, $key) : [];

        // Messages only come with a key.
        $errorId = $messages && $key !== null
            ? ($attributes->get('id') ?? 'ui-'.str_replace('.', '-', $key)).'-error'
            : null;

        return new self($key, $messages, $errorId, self::boolean($showError));
    }

    /**
     * Attribute values arrive as written: show-error="false" is a string.
     */
    private static function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    /**
     * The validation key a control is bound to: its wire:model target, or its name in dot notation.
     */
    public static function key(ComponentAttributeBag $attributes): ?string
    {
        if ($model = Model::key($attributes)) {
            return $model;
        }

        $name = $attributes->get('name');

        if (! is_string($name) || $name === '') {
            return null;
        }

        // items[0][qty] -> items.0.qty, tags[] -> tags
        return trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    }

    /**
     * Unique messages for a key, falling back to its nested keys (key.*).
     *
     * @return list<string>
     */
    public static function messages(ViewErrorBag|MessageBag|null $errors, ?string $key, string $bag = 'default'): array
    {
        if ($errors === null || $key === null || $key === '') {
            return [];
        }

        $messages = $errors instanceof ViewErrorBag ? $errors->getBag($bag) : $errors;

        return array_values(array_unique(
            $messages->get($key) ?: Arr::flatten($messages->get($key.'.*'))
        ));
    }

    /**
     * The same state keeping only some of its messages, e.g. a file list that shows
     * each file's errors on the file and only the list's own above it.
     *
     * @param  list<string>  $messages
     */
    public function only(array $messages): self
    {
        $kept = array_values(array_intersect($this->messages, $messages));

        return new self($this->key, $kept, $kept ? $this->errorId : null, $this->showError);
    }

    public function invalid(): bool
    {
        return $this->messages !== [];
    }

    public function showsError(): bool
    {
        return $this->showError && $this->invalid();
    }

    /**
     * ARIA attributes for the control itself.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_filter([
            'aria-invalid' => $this->invalid() ? 'true' : null,
            'aria-describedby' => $this->showsError() ? $this->errorId : null,
        ]);
    }
}
