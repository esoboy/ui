<?php

/*
 * Library-wide rules every component has to follow.
 */

it('enables blaze in components that do not consume native table slots', function (string $path) {
    expect(file_get_contents($path))->toStartWith('@blaze');
})->with(fn () => array_diff_key(componentViews(), array_fill_keys([
    // The class-backed table and its slot children use Laravel's component scope.
    'data-table/table.blade.php',
    'data-table/row.blade.php',
    'data-table/summary-row.blade.php',
], true)));

it('does not fold components that translate, which would freeze the compile-time locale', function (string $path) {
    $source = file_get_contents($path);

    expect(str_contains($source, '__(') && str_starts_with($source, '@blaze(fold: true'))->toBeFalse();
})->with(fn () => componentViews());

it('does not hardcode user facing text in any component', function (string $path) {
    $source = file_get_contents($path);

    $text = preg_replace([
        '/\{\{--.*?--\}\}/s',           // blade comments
        '/@php\b.*?@endphp/s',         // php blocks
        '/<\?php.*?\?>/s',              // raw php
        '/\{\{.*?\}\}|\{!!.*?!!\}/s',   // echoes, where translated text belongs
        '/<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/s', // html tags, with quoted attributes that may hold "->"
        '/@\w+(\s*\((?:[^()]|(?1))*\))?/', // directives and their arguments
    ], '', $source);

    expect(trim($text))->toBe('', "Hardcoded text found in {$path}: use a ui.* translation key instead.");
})->with(fn () => componentViews());

it('imports every component stylesheet once, in ui.css or extras.css', function () {
    $css = realpath(__DIR__.'/../../resources/css');
    $imported = [];

    foreach (['ui.css', 'extras.css'] as $entry) {
        preg_match_all("/@import '\.\/components\/([\w-]+)\.css';/", (string) file_get_contents("{$css}/{$entry}"), $matches);
        array_push($imported, ...$matches[1]);
    }

    $files = array_map(fn (string $path) => basename($path, '.css'), glob("{$css}/components/*.css") ?: []);

    sort($imported);
    sort($files);

    expect($imported)->toBe($files);
});

it('loads the extras bundle from each extra component entry', function (string $path) {
    expect(file_get_contents($path))->toContain('<x-ui::extras />');
})->with(fn () => array_intersect_key(componentViews(), array_fill_keys([
    'anchor/index.blade.php', 'back-to-top.blade.php', 'cascader.blade.php', 'color-picker.blade.php',
    'countdown.blade.php', 'cropper/index.blade.php', 'emoji-picker/index.blade.php', 'file-upload.blade.php',
    'image-preview/index.blade.php', 'image-upload.blade.php', 'mentions/index.blade.php', 'number-input.blade.php',
    'password-input.blade.php', 'rating.blade.php', 'tags-input.blade.php', 'tour/index.blade.php',
    'transfer.blade.php', 'tree-select.blade.php', 'tree/index.blade.php', 'verification-code-button.blade.php', 'watermark.blade.php',
], true)));
