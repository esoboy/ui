<?php

/*
 * Icon: the Lucide icons the package ships.
 */

use Esoboy\Ui\Support\Icons;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

it('renders a lucide icon as lucide-react does, decorative by default', function () {
    expect(trim($this->blade('<x-ui::icon name="chevron-down" class="size-4" />')))
        ->toBe('<svg class="size-4" data-slot="icon" data-size="default" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>');
});

it('renders explicit static and dynamic sizes without leaking a native size attribute', function (string $size, string $class) {
    foreach ([
        (string) $this->blade('<x-ui::icon name="plus" size="'.$size.'" />'),
        (string) $this->blade('<x-ui::icon name="plus" :size="$size" />', ['size' => $size]),
    ] as $html) {
        expect($html)->toContain('data-size="'.$size.'"', 'class="'.$class.'"')
            ->not->toContain(' size=');
    }
})->with([
    ['xs', 'size-3'], ['sm', 'size-3.5'], ['default', 'size-4'], ['lg', 'size-5'],
]);

it('leaves unspecified sizes available for container sizing', function () {
    expect((string) $this->blade('<x-ui::button size="xs"><x-ui::icon name="plus" /></x-ui::button>'))
        ->toContain('data-slot="icon" data-size="default"')
        ->not->toContain('class="size-');
});

it('folds a static name into plain svg at compile time', function () {
    expect(Blade::compileString('<x-ui::icon name="x" />'))
        ->toContainMarkup('<path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>')
        ->not->toContain('x-ui::icon');
});

it('renders a dynamic name at runtime', function () {
    expect((string) $this->blade('@foreach ($names as $name)<x-ui::icon :name="$name" />@endforeach', ['names' => ['check', 'plus']]))
        ->toContainMarkup('<path d="M20 6 9 17l-5-5"/></svg>')
        ->toContainMarkup('<path d="M5 12h14"/><path d="M12 5v14"/></svg>');
});

it('lets attributes override the defaults', function () {
    expect((string) $this->blade('<x-ui::icon name="star" fill="currentColor" stroke="none" data-icon="inline-start" />'))
        ->toContain('fill="currentColor" stroke="none"')
        ->toContain('data-icon="inline-start"')
        ->not->toContain('fill="none"');
});

it('is an image with a label', function () {
    expect((string) $this->blade('<x-ui::icon name="circle-alert" aria-label="Error" />'))
        ->toContain('aria-label="Error"')
        ->toContain('role="img"')
        ->not->toContain('aria-hidden');
});

it('rejects names outside the shipped Lucide catalogue', function (string $name) {
    expect(fn () => Icons::markup($name))->toThrow(new InvalidArgumentException("Lucide 1.62.0 has no icon [{$name}]."));
})->with(['no-such-icon', 'more-horizontal']);

it('fails the render for an unknown icon instead of showing nothing', function () {
    $this->blade('<x-ui::icon :name="$name" />', ['name' => 'no-such-icon']);
})->throws(ViewException::class, 'has no icon [no-such-icon]');
