<?php

namespace Esoboy\Ui\Support;

/**
 * A control whose error message its wrapper renders instead.
 *
 * An input inside <x-ui::input-group> can't put its error right after itself (it
 * would land inside the group's row). Blade renders the group's slot first, so the
 * input leaves its state here and the group, rendering next, shows the error after itself.
 */
final class PendingControl
{
    private ?ControlState $state = null;

    private string $text = '';

    private ?int $maxlength = null;

    public static function put(ControlState $state, mixed $value = null, mixed $maxlength = null): void
    {
        $pending = app(self::class);
        $pending->state = $state;
        $pending->text = is_scalar($value) ? (string) $value : '';
        $pending->maxlength = is_numeric($maxlength) && $maxlength > 0 ? (int) $maxlength : null;
    }

    /** @return array{count: int, max: ?int, value: string} */
    public static function characters(?int $max = null): array
    {
        $pending = app(self::class);

        return ['count' => mb_strlen($pending->text), 'max' => $max ?? $pending->maxlength, 'value' => $pending->text];
    }

    public static function take(): ?ControlState
    {
        $pending = app(self::class);
        $state = $pending->state;
        $pending->state = null;
        $pending->text = '';
        $pending->maxlength = null;

        return $state;
    }
}
