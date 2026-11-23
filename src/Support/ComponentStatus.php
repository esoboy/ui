<?php

namespace Esoboy\Ui\Support;

final class ComponentStatus
{
    public static function cssClass(mixed $status): ?string
    {
        return in_array($status, ['neutral', 'info', 'success', 'warning', 'error'], true)
            ? 'ui-status-'.$status
            : null;
    }
}
