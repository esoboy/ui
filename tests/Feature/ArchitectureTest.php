<?php

it('uses Alpine lifecycles instead of a custom native JavaScript runtime', function () {
    $root = realpath(__DIR__.'/../../resources/js');
    expect(glob($root.'/components/*.js'))->toBeEmpty();
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() !== 'js') {
            continue;
        }
        $source = file_get_contents($file->getPathname());
        expect($source)->not->toMatch('/\bcustomElements\b|\bextends\s+HTMLElement\b|\bdispatchEvent\s*\(/');
        // SignaturePad exposes its own SDK event API; no DOM listener is registered here.
        $source = preg_replace("/this\._pad\.addEventListener\('(beginStroke|endStroke)'/", 'sdkEvent(', $source);
        expect($source)->not->toMatch('/\b(?:addEventListener|removeEventListener)\s*\(|\.on(?:load|error|click|input|change|keydown|pointer\w+)\s*=/');
    }
});

it('keeps initialization and internal event wiring out of demo call sites', function () {
    foreach (glob(__DIR__.'/../../../application/resources/views/pages/*.blade.php') as $path) {
        $source = file_get_contents($path);
        expect($source)->not->toMatch('/\bx-(?:data|init|ref|on:)[\w:.-]*\s*=|<script\b/i');
    }
});

it('does not expose removed frontend default properties or obsolete parts', function () {
    foreach (componentViews() as $path) {
        expect(file_get_contents($path))
            ->not->toMatch('/[\'"](?:default[A-Z]\w*|isActive|shouldFilter)[\'"]\s*=>/')
            ->not->toMatch('/@props\s*\(\s*\[[^\]]*[\'"](?:as|asChild|as-child)[\'"]\s*(?:=>|,|\])/s', $path);
    }
    expect(view()->exists('ui::components.combobox.chips'))->toBeFalse()
        ->and(view()->exists('ui::components.questionnaire.item'))->toBeFalse()
        ->and(view()->exists('ui::components.tree.item'))->toBeFalse();
});

it('renders component content in Blade', function () {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../resources/views'));

    foreach ($files as $file) {
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            expect(file_get_contents($file->getPathname()))->not->toMatch('/\bx-(for|html|text)\b/', $file->getPathname());
        }
    }
});

it('keeps data rendering out of the migrated browser providers', function (string $provider) {
    $source = file_get_contents(__DIR__.'/../../resources/js/'.$provider.'.js');
    expect($source)->not->toMatch('/\.innerHTML\s*=|\.textContent\s*=(?!=)|\.replaceChildren\s*\(|\.cloneNode\s*\(|document\.createElement\s*\(/');
})->with([
    'alpine/select', 'alpine/command', 'alpine/file-upload', 'alpine/toast',
    'alpine/tour', 'alpine/confirm', 'alpine/countdown', 'alpine/color-picker',
    'alpine/image-preview', 'alpine/emoji-picker', 'heavy/editor/suggest', 'heavy/editor/toolbar',
]);
