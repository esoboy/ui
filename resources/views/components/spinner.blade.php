@blaze
@props(['color' => null])
<x-ui::icon :color="$color" name="loader-circle" :attributes="$attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'spinner', 'role' => 'status', 'aria-label' => __('ui::ui.loading'), 'aria-hidden' => 'false'])" />
