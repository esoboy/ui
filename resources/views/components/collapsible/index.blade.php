@props(['open' => false])
@includeIsolated('ui::partials.panel-state', ['kind' => 'collapsible', 'initial' => $open, 'settings' => [], 'attributes' => $attributes, 'slot' => $slot])
