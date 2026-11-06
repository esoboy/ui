<?php

/*
 * File Upload and Image Upload.
 */

use Esoboy\Ui\Support\UploadedFiles;
use Esoboy\Ui\Tests\Fixtures\Uploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => Storage::fake('tmp-for-tests'));

it('renders pending upload progress and resets after cancellation', function () {
    $component = Livewire::test('ui.pending-upload', ['list' => 'pending-list'])
        ->call('begin', 1, [['name' => 'notes.txt', 'size' => 2048]])
        ->call('advance', 1, 42);

    expect($component->html())
        ->toContainMarkup('<div role="progressbar" aria-valuenow="42">')
        ->toContainMarkup('<span data-slot="attachment-title">notes.txt</span>')
        ->toContain('2 KB', 'Uploading… 42%');

    $component->call('finish', 1, 'cancel');
    expect($component->html())->not->toContainMarkup('<span data-slot="attachment-title">notes.txt</span>');
});

it('clears failed uploads and accepts a new operation without stale progress', function () {
    $component = Livewire::test('ui.pending-upload', ['list' => 'pending-list'])
        ->call('begin', 1, [['name' => 'first.txt'], ['name' => 'second.txt']])
        ->call('finish', 1, 'error')
        ->assertSet('phase', 'error')
        ->assertSet('files', [])
        ->call('begin', 2, [['name' => 'retry.txt']])
        ->call('advance', 1, 100)
        ->call('finish', 1, 'cancel')
        ->assertSet('phase', 'uploading')
        ->assertSet('progress', 0);

    expect($component->html())->toContain('retry.txt')->not->toContain('first.txt', 'second.txt');

    $component->call('finish', 2, 'finish')->assertSet('files', []);
});

it('renders a drop area around the file input', function () {
    $view = $this->blade('<x-ui::file-upload wire:model="files" multiple accept="image/*" description="PNG up to 2 MB" class="max-w-md" />');

    $view->assertSeeMarkupInOrder([
        '<eso-file-upload data-slot="file-upload" data-orientation="horizontal" class="max-w-md">',
        '<div data-slot="file-upload-dropzone" >',
        '<input type="file" data-slot="file-upload-input" multiple="multiple" accept="image/*" wire:model="files">',
        '<span data-slot="file-upload-title">Drop files here or click to upload</span>',
        '<span data-slot="file-upload-description">PNG up to 2 MB</span>',
        '<div data-slot="attachment-group" data-file-upload-list="data-file-upload-list">',
        '<div role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-valuetext="0%" data-slot="progress"',
        '<div data-file-upload-pending>',
    ]);
});

it('takes its own content and title', function () {
    $this->blade('<x-ui::file-upload title="Drop the contract" disabled />')
        ->assertSeeMarkup('<span data-slot="file-upload-title">Drop the contract</span>')
        ->assertSee('disabled="disabled"', false);

    $this->blade('<x-ui::file-upload>Custom</x-ui::file-upload>')
        ->assertSee('Custom', false)
        ->assertDontSee('file-upload-title', false);
});

it('lists the uploaded files with previews and remove buttons', function () {
    $component = Livewire::test(Uploader::class)
        ->set('files', [UploadedFile::fake()->image('cover.png', 20, 20), UploadedFile::fake()->create('notes.pdf', 3, 'application/pdf')]);

    $html = $component->html();
    $files = $component->get('files');

    expect($html)
        ->toContain('<div data-slot="attachment" data-state="done" data-size="default" data-orientation="horizontal" wire:key="upload-'.$files[0]->getFilename().'">')
        ->toContainMarkup('<span data-slot="attachment-title">cover.png</span>')
        ->toContainMarkup('<span data-slot="attachment-title">notes.pdf</span>')
        ->toContainMarkup('<span data-slot="attachment-description">3 KB</span>')
        ->toMatch('/<div data-slot="attachment-media" data-variant="image">(?:\s|<!--.*?-->)*<img src="[^"]+preview-file[^"]+" alt="">/')
        ->toContain("wire:click=\"\$removeUpload('files', '".$files[1]->getFilename()."')\"")
        ->not->toMatch('/data-file-upload-list\s+hidden/');
});

it('shows each file\'s error on that file, and the list\'s own error below it', function () {
    $component = Livewire::test(Uploader::class)
        ->set('files', [UploadedFile::fake()->create('big.pdf', 50)])
        ->call('check');

    $html = $component->html();

    expect($html)->toContain('data-state="error"')
        ->toMatch('/<span data-slot="attachment-description">The files.0 field must not be greater than 1 kilobytes.<\/span>/')
        ->not->toContain('data-slot="field-error"');

    expect(Livewire::test(Uploader::class)->call('check')->html())
        ->toContain('data-slot="field-error"')
        ->toContain('The files field is required.');
});

it('shows the current image, or the new upload with a remove button', function () {
    $component = Livewire::test(Uploader::class);

    expect($component->html())
        ->toContainMarkup('<eso-file-upload data-slot="image-upload" data-shape="circle">')
        ->toContainMarkup('<img data-slot="image-upload-image" src="/old.png" alt="" >')
        ->toContainMarkup('<span data-slot="image-upload-placeholder"  hidden >')
        ->not->toContain('data-image-upload-remove');

    $component->set('avatar', UploadedFile::fake()->image('me.png'));

    expect($component->html())
        ->not->toContain('src="/old.png"')
        ->toContain('data-image-upload-remove')
        ->toContain("\$removeUpload('avatar', '".$component->get('avatar')->getFilename()."')");
});

it('formats file sizes', function () {
    expect(UploadedFiles::size(0))->toBe('0 B')
        ->and(UploadedFiles::size(512))->toBe('512 B')
        ->and(UploadedFiles::size(2048))->toBe('2 KB')
        ->and(UploadedFiles::size(1258291))->toBe('1.2 MB');
});
