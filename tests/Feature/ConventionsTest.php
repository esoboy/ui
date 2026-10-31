<?php

/*
 * Library-wide rules every component has to follow.
 */

it('enables blaze in every component', function (string $path) {
    expect(file_get_contents($path))->toStartWith('@blaze');
})->with(fn () => componentViews());

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

it('loads extras.js wherever an element it defines shows', function (string $path) {
    $js = realpath(__DIR__.'/../../resources/js');

    preg_match_all("/from '\.\/components\/([\w-]+)\.js'/", (string) file_get_contents("{$js}/extras.js"), $modules);

    $elements = collect($modules[1])
        ->flatMap(function (string $module) use ($js) {
            preg_match_all("/define\('(eso-[\w-]+)'/", (string) file_get_contents("{$js}/components/{$module}.js"), $defined);

            return $defined[1];
        });

    $source = (string) file_get_contents($path);

    expect(str_contains($source, '<x-ui::extras />'))->toBe($elements->contains(fn (string $element) => str_contains($source, "<{$element}")));
})->with(fn () => array_diff_key(componentViews(), ['extras.blade.php' => true]));
