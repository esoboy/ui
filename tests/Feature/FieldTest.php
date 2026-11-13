<?php

it('renders the field family with shadcn slots', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::field.set>
            <x-ui::field.legend variant="label">Profile</x-ui::field.legend>
            <x-ui::field.group>
                <x-ui::field orientation="horizontal">
                    <x-ui::field.content>
                        <x-ui::field.title>Title</x-ui::field.title>
                        <x-ui::field.label for="name">Name</x-ui::field.label>
                        <x-ui::field.description>Help</x-ui::field.description>
                    </x-ui::field.content>
                </x-ui::field>
            </x-ui::field.group>
        </x-ui::field.set>
        BLADE);

    $view->assertSeeMarkup('<fieldset data-slot="field-set">')
        ->assertSeeMarkup('<legend data-slot="field-legend" data-variant="label">Profile</legend>')
        ->assertSeeMarkup('<div data-slot="field-group">')
        ->assertSeeMarkup('<div role="group" data-slot="field" data-orientation="horizontal">')
        ->assertSeeMarkup('<div data-slot="field-content">')
        ->assertSeeMarkup('<div data-slot="field-title">Title</div>')
        ->assertSeeMarkup('<label data-slot="field-label" for="name">Name</label>')
        ->assertSeeMarkup('<p data-slot="field-description">Help</p>');
});

it('defaults to a vertical field', function () {
    $this->blade('<x-ui::field>x</x-ui::field>')->assertSee('data-orientation="vertical"', false);
});

it('renders a separator with and without content', function () {
    $this->blade('<x-ui::field.separator />')
        ->assertSee('data-content="false"', false)
        ->assertDontSee('field-separator-content', false);

    $this->blade('<x-ui::field.separator>Or</x-ui::field.separator>')
        ->assertSee('data-content="true"', false)
        ->assertSeeMarkup('<span data-slot="field-separator-content">Or</span>');
});

it('renders nothing for an error without messages', function () {
    expect(trim((string) $this->blade('<x-ui::field.error name="email" />')))->toBe('');
});

it('renders a single message as text and several as a list', function () {
    $this->withViewErrors(['email' => 'One.'])
        ->blade('<x-ui::field.error name="email" />')
        ->assertSee('One.')
        ->assertDontSee('<ul>', false);

    $this->blade('<x-ui::field.error :messages="[\'One.\', \'Two.\']" />')
        ->assertSeeMarkupInOrder(['<ul>', '<li>One.</li>', '<li>Two.</li>']);
});

it('prefers its own slot over messages', function () {
    $this->withViewErrors(['email' => 'From the bag.'])
        ->blade('<x-ui::field.error name="email">Custom</x-ui::field.error>')
        ->assertSee('Custom')
        ->assertDontSee('From the bag.');
});

it('reads a named error bag', function () {
    $this->withViewErrors(['email' => 'Taken.'], 'register')
        ->blade('<x-ui::field.error name="email" bag="register" />')
        ->assertSee('Taken.');
});

it('renders a label and separators', function () {
    $this->blade('<x-ui::label for="x">Name</x-ui::label>')
        ->assertSeeMarkup('<label data-slot="label" for="x">Name</label>');

    $this->blade('<x-ui::separator />')
        ->assertSeeMarkup('<div data-slot="separator" data-orientation="horizontal" role="none"></div>');

    $this->blade('<x-ui::separator orientation="vertical" :decorative="false" />')
        ->assertSeeMarkup('<div data-slot="separator" data-orientation="vertical" role="separator" aria-orientation="vertical"></div>');
});
