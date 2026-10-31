<?php

use Esoboy\Ui\Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * Values of an attribute, in document order.
 *
 * @return list<string>
 */
function idsOf(string $html, string $attribute): array
{
    preg_match_all('/\s'.$attribute.'="([^"]+)"/', $html, $matches);

    return $matches[1];
}

/**
 * Every component view shipped by the package, keyed by its path relative to the components directory.
 *
 * @return array<string, string>
 */
function componentViews(): array
{
    $root = realpath(__DIR__.'/../resources/views/components');

    return collect(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)))
        ->filter(fn (SplFileInfo $file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->mapWithKeys(fn (SplFileInfo $file) => [
            str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1)) => $file->getPathname(),
        ])
        ->sortKeys()
        ->all();
}
