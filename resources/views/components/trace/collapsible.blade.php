@props(['initialOpen' => false])
@includeIsolated('ui::partials.panel-state', ['kind' => 'trace', 'initial' => $initialOpen, 'settings' => [], 'attributes' => $attributes, 'slot' => $slot])
