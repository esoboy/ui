@includeIsolated('ui::partials.step', ['direction' => 1, 'attributes' => $attributes->merge(['data-ui-step-disabled' => '!canNext'])->class('ui-carousel-next'), 'slot' => $slot])
