<?php

use Esoboy\Ui\Livewire\EmojiPicker;
use Livewire\Livewire;

it('renders localized emoji choices on the server with an optional footer', function () {
    $html = (string) $this->blade('<x-ui::emoji-picker class="h-[342px]" footer />');
    expect($html)->toContainMarkup('<eso-emoji-picker data-slot="emoji-picker" data-columns="9" class="h-[342px]">')->toContain('data-emoji-picker-search')->toContain('data-slot="emoji-picker-viewport"')->toContain('aria-label="Grinning face"')->toContain('😀')->toContain('Select an emoji…')->not->toContain('<template');
});

it('passes search classes to its wrapper and attributes to the input', function () {
    $this->blade('<x-ui::emoji-picker.search class="h-10" placeholder="Find" id="emoji-search" />')->assertSeeMarkup('<div data-slot="emoji-picker-search-wrapper" class="h-10">')->assertSeeMarkup('<input data-slot="input-group-control" id="emoji-search" placeholder="Find">');
});

it('lays out the columns requested', function () {
    $this->blade('<x-ui::emoji-picker :columns="8" />')->assertSee('data-columns="8"', false)->assertSee('--emoji-picker-columns: 8;', false);
});

it('renders Chinese emoji data and labels under zh_CN', function () {
    app()->setLocale('zh_CN');
    $this->blade('<x-ui::emoji-picker footer />')->assertSee('placeholder="搜索…"', false)->assertSee('笑脸与情感')->assertSee('aria-label="笑脸"', false)->assertSee('选择一个表情…');
});

it('uses English emoji data when the build has no catalog for a locale', function () {
    app()->setLocale('ja');
    $this->blade('<x-ui::emoji-picker />')->assertSee('aria-label="Grinning face"', false);
});

it('filters and validates emoji choices in PHP', function () {
    Livewire::test(EmojiPicker::class)->set('query', 'love')->assertSee('❤️')->assertDontSeeHtml('aria-label="Grinning face"')->call('pick', 'invalid')->assertSet('value', null)->call('pick', '❤️')->assertSet('value', '❤️')->assertDispatched('emoji-select', emoji: '❤️', label: 'Heart');
});
