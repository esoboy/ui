<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Carousel extends Component
{
    private int $slides = 0;

    public function registerCarouselSlide(): int
    {
        return ++$this->slides;
    }

    public function carouselSlideCount(): int
    {
        return $this->slides;
    }

    public function render(): View
    {
        return view('ui::components.carousel.index');
    }
}
