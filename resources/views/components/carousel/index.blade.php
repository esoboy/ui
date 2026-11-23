@props(['color' => null, 'orientation' => 'horizontal', 'loop' => false, 'align' => 'center', 'controls' => true, 'pagination' => 'dots'])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'carousel', $attributes);
    $count = $carouselSlideCount();
    if (!in_array($pagination, ['dots', 'numbers', 'fraction', 'none'], true)) {
        throw new \InvalidArgumentException(__('ui::errors.carousel_pagination'));
    }
@endphp
<section {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'carousel', 'data-orientation' => $orientation, 'aria-roledescription' => __('ui::ui.carousel')]) }}
    x-data="uiCarousel(@js($orientation), @js($loop), @js($align))" x-on:keydown="navigate($event)">
    {{ $slot }}
    @if ($count > 1 && ($controls || isset($indicators) || $pagination !== 'none'))
        <div data-esoboy-ui data-slot="carousel-controls" x-show="pages > 1" x-cloak>
            @if ($controls)
                <x-ui::carousel.previous><x-ui::button size="icon-sm" variant="outline" class="ui-carousel-arrow" :aria-label="__('ui::ui.previous_slide')">
                    <x-ui::icon :name="$orientation === 'vertical' ? 'chevron-up' : 'chevron-left'" />
                </x-ui::button></x-ui::carousel.previous>
            @endif
            @isset ($indicators)
                {{ $indicators }}
            @elseif ($pagination === 'fraction')
                <div data-esoboy-ui data-slot="carousel-fraction" aria-live="polite" aria-atomic="true" aria-label="{{ __('ui::ui.pagination') }}">
                    @for ($page = 1; $page <= $count; $page++)
                        <span data-ui-carousel-current x-show="current === {{ $page }}">{{ $page }}</span>
                    @endfor
                    <span aria-hidden="true">/</span>
                    @for ($page = 1; $page <= $count; $page++)
                        <span x-show="pages === {{ $page }}">{{ $page }}</span>
                    @endfor
                </div>
            @elseif ($pagination !== 'none')
                <div data-esoboy-ui data-slot="carousel-pagination" data-mode="{{ $pagination }}" role="group" aria-label="{{ __('ui::ui.pagination') }}">
                    @for ($page = 1; $page <= $count; $page++)
                        <x-ui::carousel.page :page="$page">
                            @if ($pagination === 'dots')<span class="ui-carousel-dot" aria-hidden="true"></span>@else{{ $page }}@endif
                        </x-ui::carousel.page>
                    @endfor
                </div>
            @endisset
            @if ($controls)
                <x-ui::carousel.next><x-ui::button size="icon-sm" variant="outline" class="ui-carousel-arrow" :aria-label="__('ui::ui.next_slide')">
                    <x-ui::icon :name="$orientation === 'vertical' ? 'chevron-down' : 'chevron-right'" />
                </x-ui::button></x-ui::carousel.next>
            @endif
        </div>
    @endif
</section>
