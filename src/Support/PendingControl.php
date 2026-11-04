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

    public static function put(ControlState $state): void
    {
        app(self::class)->state = $state;
    }

    public static function take(): ?ControlState
    {
        $pending = app(self::class);
        $state = $pending->state;
        $pending->state = null;

        return $state;
    }
}
