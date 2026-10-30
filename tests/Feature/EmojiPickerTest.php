<?php

/*
 * Emoji Picker: shadcn's registry picker; the emojis come from the build's data files.
 */

it('renders the parts the script fills, with the data for the app\'s language', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::emoji-picker class="h-[342px]" wire:emoji-select="react($event.detail.emoji)">
            <x-ui::emoji-picker.search />
            <x-ui::emoji-picker.content />
            <x-ui::emoji-picker.footer />
        </x-ui::emoji-picker>
        BLADE);

    expect($html)
        ->toContain('<eso-emoji-picker data-slot="emoji-picker" data-src="http://localhost/esoboy-ui/emoji/en.json?id=1111aaaa2222" data-columns="9" style="--emoji-picker-columns: 9;" class="h-[342px]" wire:emoji-select="react($event.detail.emoji)">')
        ->toContain('<div data-slot="emoji-picker-search-wrapper" wire:ignore>')
        ->toContain('<input type="text" data-slot="emoji-picker-search" placeholder="Search…" aria-label="Search…" autocomplete="off" spellcheck="false">')
        ->toContain('<div data-slot="emoji-picker-viewport" wire:ignore>')
        ->toContain('<div data-slot="emoji-picker-empty" hidden>No emoji found.</div>')
        ->toContain('<div data-slot="emoji-picker-list" role="listbox" aria-label="Emojis" hidden>')
        ->toContain('<div data-slot="emoji-picker-category" data-category="smileys-emotion" role="group" aria-label="Smileys &amp; emotion">')
        ->toContain('<div data-slot="emoji-picker-category-header" aria-hidden="true">Smileys &amp; emotion</div>')
        ->toContain('<template data-template="emoji"><button type="button" role="option" aria-selected="false" tabindex="-1" data-slot="emoji-picker-emoji"></button></template>')
        ->toContain('<div data-slot="emoji-picker-footer" wire:ignore>')
        ->toContain('<span data-slot="emoji-picker-placeholder">Select an emoji…</span>')
        ->and(substr_count($html, 'data-slot="emoji-picker-category"'))->toBe(9)
        ->and($html)->not->toContain('data-category="component"');
});

it('passes the class to the search wrapper and the rest to the input', function () {
    $this->blade('<x-ui::emoji-picker.search class="h-10" placeholder="Find" id="emoji-search" />')
        ->assertSee('<div data-slot="emoji-picker-search-wrapper" class="h-10" wire:ignore>', false)
        ->assertSee('<input type="text" data-slot="emoji-picker-search" placeholder="Find" aria-label="Search…" autocomplete="off" spellcheck="false" id="emoji-search">', false);
});

it('lays out the columns asked for', function () {
    $this->blade('<x-ui::emoji-picker :columns="8" />')
        ->assertSee('data-columns="8" style="--emoji-picker-columns: 8;"', false);
});

it('loads Chinese emoji data and labels under zh_CN', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::emoji-picker><x-ui::emoji-picker.search /><x-ui::emoji-picker.content /><x-ui::emoji-picker.footer /></x-ui::emoji-picker>')
        ->assertSee('data-src="http://localhost/esoboy-ui/emoji/zh.json?id=3333bbbb4444"', false)
        ->assertSee('placeholder="搜索…"', false)
        ->assertSee('<div data-slot="emoji-picker-category-header" aria-hidden="true">笑脸与情感</div>', false)
        ->assertSeeInOrder(['没有找到表情。', '选择一个表情…']);
});

it('falls back to English data for a language the build has none for', function () {
    app()->setLocale('ja');

    $this->blade('<x-ui::emoji-picker />')
        ->assertSee('data-src="http://localhost/esoboy-ui/emoji/en.json?id=1111aaaa2222"', false);
});
