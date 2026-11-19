@aware(['registerCarouselSlide'])
@php($number = $registerCarouselSlide())
<div {{ $attributes->merge(['data-slot' => 'carousel-item', 'role' => 'group', 'aria-roledescription' => __('ui::ui.slide'), 'aria-label' => __('ui::ui.slide_number', ['number' => $number])]) }}>
    {{ $slot }}
</div>
