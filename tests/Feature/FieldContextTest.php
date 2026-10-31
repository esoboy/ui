<?php

use Esoboy\Ui\Support\FieldContext;

it('marks field and field set tags', function (string $tag, string $marked) {
    expect(FieldContext::mark($tag))->toBe($marked);
})->with([
    'field' => ['<x-ui::field>', '<x-ui::field uiField>'],
    'field with attributes' => ['<x-ui::field orientation="horizontal">', '<x-ui::field uiField orientation="horizontal">'],
    'self-closing' => ['<x-ui::field/>', '<x-ui::field uiField/>'],
    'multiline' => ["<x-ui::field\n    orientation=\"horizontal\">", "<x-ui::field uiField\n    orientation=\"horizontal\">"],
    'field set' => ['<x-ui::field.set>', '<x-ui::field.set uiField>'],
]);

it('leaves other tags alone', function (string $tag) {
    expect(FieldContext::mark($tag))->toBe($tag);
})->with([
    'closing tag' => ['</x-ui::field>'],
    'field label' => ['<x-ui::field.label for="x">'],
    'field error' => ['<x-ui::field.error name="x" />'],
    'field group' => ['<x-ui::field.group>'],
    'field separator' => ['<x-ui::field.separator />'],
    'plain text' => ['x-ui::field'],
]);

it('does not leak the marker into the html', function () {
    $this->blade('<x-ui::field><x-ui::field.set>x</x-ui::field.set></x-ui::field>')
        ->assertDontSee('uiField', false)
        ->assertDontSee('ui-field', false)
        ->assertDontSee('show-error', false);
});

it('lets the nearest field decide whether messages show', function () {
    $view = $this->withViewErrors(['a' => 'Error A.', 'b' => 'Error B.'])
        ->blade(<<<'BLADE'
            <x-ui::field.set :show-error="false">
                <x-ui::field><x-ui::input wire:model="a" /></x-ui::field>
                <x-ui::field :show-error="true"><x-ui::input wire:model="b" /></x-ui::field>
            </x-ui::field.set>
            BLADE);

    $view->assertDontSee('Error A.')->assertSee('Error B.');

    expect(substr_count($view, 'aria-invalid="true"'))->toBe(2);
});

it('accepts show-error written as a plain string', function () {
    $this->withViewErrors(['a' => 'Error A.'])
        ->blade('<x-ui::field show-error="false"><x-ui::input wire:model="a" /></x-ui::field>')
        ->assertSee('aria-invalid="true"', false)
        ->assertDontSee('Error A.');
});

it('validates controls nested deeper inside a field', function () {
    $this->withViewErrors(['a' => 'Error A.'])
        ->blade('<x-ui::field><x-ui::field.content><x-ui::input wire:model="a" /></x-ui::field.content></x-ui::field>')
        ->assertSee('Error A.');
});

it('forgets the field once it closes', function () {
    $this->withViewErrors(['a' => 'Error A.', 'b' => 'Error B.'])
        ->blade('<x-ui::field><x-ui::input wire:model="a" /></x-ui::field><x-ui::input wire:model="b" />')
        ->assertSee('Error A.')
        ->assertDontSee('Error B.');
});
