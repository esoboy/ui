<?php

/*
 * Mentions: a Textarea that suggests its items after a prefix.
 */

it('puts the textarea in charge and the items in a listbox for the caret', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::mentions wire:model="comment" placeholder="Write a comment" rows="3" class="min-h-24">
            <x-ui::mentions.item value="zhangsan">张三</x-ui::mentions.item>
            <x-ui::mentions.item value="wangwu" disabled>王五</x-ui::mentions.item>
        </x-ui::mentions>
        BLADE);
    expect($html)
        ->toContain('<eso-mentions data-slot="mentions" data-prefix="[&quot;@&quot;]" data-split=" ">')
        ->toContain('<textarea data-slot="textarea" class="min-h-24" role="combobox" aria-autocomplete="list" aria-expanded="false" data-mentions-input="data-mentions-input" wire:model="comment" placeholder="Write a comment" rows="3"></textarea>')
        ->toContain('<div popover="manual" role="listbox" data-slot="mentions-content" wire:ignore.self>')
        ->toContain('<div role="option" wire:key="mentions-item-zhangsan" data-slot="mentions-item" data-value="zhangsan" aria-selected="false">张三</div>')
        ->toContain('data-value="wangwu" aria-selected="false" aria-disabled="true"')
        ->toContain('<div data-slot="mentions-empty">No results found.</div>')
        ->toContain('<div data-slot="mentions-mirror" aria-hidden="true"><span></span><span data-mentions-caret></span></div>');
});

it('takes several prefixes and items for one of them', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::mentions :prefix="['@', '#']" split="," wire:model="tags">
            <x-ui::mentions.item value="design" prefix="#">Design</x-ui::mentions.item>
        </x-ui::mentions>
        BLADE);

    expect($html)
        ->toContain('data-prefix="[&quot;@&quot;,&quot;#&quot;]" data-split=","')
        ->toContain('data-value="design" data-prefix="#"');
});

it('validates its textarea inside a field', function () {
    $this->withViewErrors(['comment' => 'Say something.'])
        ->blade('<x-ui::field><x-ui::mentions id="comment" wire:model="comment" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="comment-error"', false)
        ->assertSeeInOrder(['</textarea>', 'Say something.', '</eso-mentions>'], false);
});
