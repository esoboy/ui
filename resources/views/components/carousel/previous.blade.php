@includeIsolated('ui::partials.step', ['direction' => -1, 'attributes' => $attributes->merge(['data-ui-step-disabled' => '!canPrevious'])->class('ui-carousel-previous'), 'slot' => $slot])
