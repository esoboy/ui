<?php

namespace Esoboy\Ui\Support;

use Closure;
use Dom\HTMLElement;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;

final class ControlSlot
{
    public static function dialog(string $slot, ComponentAttributeBag $attributes, string $target, string $command): string
    {
        return self::bind($slot, $attributes, function (HTMLElement $control) use ($target, $command): array {
            $binding = $command === 'show-modal' ? ['aria-haspopup' => 'dialog', 'aria-controls' => $target] : [];

            if ($control->localName === 'button') {
                return $binding + ['commandfor' => $target, 'command' => $command];
            }

            $dialog = '$el.ownerDocument.getElementById('.Js::from($target).')';
            $action = $command === 'show-modal'
                ? '?.showModal()'
                : '?.close($el.getAttribute("value") ?? "")';

            return $binding + [
                'x-data' => $control->getAttribute('x-data') ?? true,
                'x-on:click.prevent' => '!$el.matches(\':disabled,[aria-disabled="true"]\') && '.$dialog.$action,
            ];
        });
    }

    /** @param  array<string, mixed>  $options */
    public static function overlay(string $slot, ComponentAttributeBag $attributes, string $kind, ?string $target, array $options = []): string
    {
        return self::bind($slot, $attributes, function (HTMLElement $control) use ($kind, $target, $options): array {
            $marker = 'data-'.$kind.'-trigger';
            $binding = [
                $marker => $marker,
                'data-ui-'.$kind => $target,
                'x-ui-overlay-trigger' => true,
                ...$options,
            ];

            if (in_array($kind, ['popover', 'dropdown-menu'], true)) {
                $binding['aria-controls'] = $target;
                if ($kind === 'dropdown-menu') {
                    $binding['aria-haspopup'] = 'menu';
                }
                if ($control->localName === 'button') {
                    $binding['command'] = 'toggle-popover';
                    $binding['commandfor'] = $target;
                }
            }

            return $binding;
        });
    }

    /**
     * Attach behavior to the actual control so its focus, attributes and layout stay intact.
     *
     * @param  array<string, mixed>|Closure(HTMLElement): array<string, mixed>  $behavior
     */
    public static function bind(string $slot, ComponentAttributeBag $attributes, array|Closure $behavior): string
    {
        $document = HtmlFragment::parse($slot);
        $body = $document->body;
        $control = $body?->firstElementChild;

        if (! $control instanceof HTMLElement || $control->nextElementSibling !== null) {
            throw new InvalidArgumentException('A UI behavior slot must contain one rendered control.');
        }

        $existing = [];
        foreach ($control->attributes as $attribute) {
            $existing[$attribute->name] = $attribute->value;
        }
        // Blade escapes forwarded values; DOM attributes need their decoded values.
        // Existing DOM attributes and generated behavior are already decoded.
        $forwarded = array_map(
            fn ($value) => is_string($value) ? html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $value,
            $attributes->getAttributes(),
        );
        $merged = (new ComponentAttributeBag($existing))->merge($forwarded, escape: false)->getAttributes();
        $binding = $behavior instanceof Closure ? $behavior($control) : $behavior;
        foreach (array_replace($merged, $binding) as $name => $value) {
            if ($value === false || $value === null) {
                $control->removeAttribute($name);

                continue;
            }

            if (($existing[$name] ?? null) !== $value) {
                $control->setAttribute($name, $value === true ? '' : (string) $value);
            }
        }

        return $document->saveHtml($control);
    }
}
