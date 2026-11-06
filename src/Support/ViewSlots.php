<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentSlot;
use Livewire\LivewireManager;

final class ViewSlots
{
    /**
     * Pass Blade regions through Livewire's fragment protocol so the component
     * that supplied them keeps control of their updates and actions.
     *
     * @param  array<string, mixed>  $parameters
     * @param  array<string, ComponentSlot|null>  $slots
     */
    public static function mount(string $name, array $parameters, string $key, array $slots): string
    {
        $fragments = [];
        foreach ($slots as $slotName => $slot) {
            if ($slot !== null) {
                $fragments[$slotName] = $slot->toHtml();
            }
        }

        return app(LivewireManager::class)->mount($name, $parameters, $key, $fragments);
    }
}
