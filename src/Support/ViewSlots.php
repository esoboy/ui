<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentSlot;
use Livewire\Features\SupportNestingComponents\SupportNestingComponents;
use Livewire\Features\SupportSlots\Slot;
use Livewire\LivewireManager;

use function Livewire\after;
use function Livewire\store;

final class ViewSlots
{
    public static function boot(): void
    {
        // Livewire's browser children list follows DOM ancestry. Slot children are
        // mounted by their caller but live inside a control; preserve those keys in
        // signed memo so the caller can reuse them on its next render.
        after('hydrate', function ($component, $memo) {
            if (isset($memo['uiSlotChildren'])) {
                SupportNestingComponents::setPreviouslyRenderedChildren($component, array_replace($memo['uiSlotChildren'], $memo['children'] ?? []));
                store($component)->set('uiPreviousSlotChildren', $memo['uiSlotChildren']);
            }
        });
        after('dehydrate', function ($component, $context) {
            $children = store($component)->get($component->shouldSkipRender() ? 'uiPreviousSlotChildren' : 'uiSlotChildren');
            if ($children) {
                $context->addMemo('uiSlotChildren', $children);
            }
        });
    }

    public static function capture(ComponentSlot $slot): string
    {
        $content = $slot->toHtml();
        $owner = Model::component();
        if ($owner && str_contains($content, 'wire:id=')) {
            $document = HtmlFragment::parse($content);
            foreach ($document->querySelectorAll('[wire\\:id][wire\\:key]') as $child) {
                if (! $child->parentElement?->closest('[wire\\:id]')) {
                    store($owner)->push('uiSlotChildren', [$child->localName, $child->getAttribute('wire:id')], $child->getAttribute('wire:key'));
                }
            }
        }

        return $content;
    }

    /**
     * Server-transformed parts must be rendered again by their control, while
     * their application directives still belong to the caller's Livewire scope.
     */
    public static function render(string $content, string $componentId, ?string $ownerId): string
    {
        return (new Slot('default', $content, $componentId, $ownerId))->toHtml();
    }

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
                $fragments[$slotName] = self::capture($slot);
            }
        }

        return app(LivewireManager::class)->mount($name, $parameters, $key, $fragments);
    }
}
