<?php

use Esoboy\Ui\Tests\TestCase;
use Illuminate\Testing\TestView;
use PHPUnit\Framework\Assert;

pest()->extend(TestCase::class)->in('Feature');

/** Match HTML attributes independently of their order and internal Alpine/Livewire attributes. */
function htmlPattern(string $fragment): string
{
    $parts = preg_split('/(<[a-z][^>]*>)/i', $fragment, flags: PREG_SPLIT_DELIM_CAPTURE);
    $pattern = '';
    foreach ($parts as $part) {
        if (! preg_match('/^<([\w:-]+)\s*(.*?)\/?>(?=$)/s', $part, $tag)) {
            $pattern .= preg_quote($part, '~');

            continue;
        }
        $pattern .= '<'.preg_quote($tag[1], '~').'(?=[\s/>])';
        preg_match_all('/([\w:.-]+)(?:\s*=\s*("[^"]*"|\'[^\']*\'))?/', $tag[2], $attributes, PREG_SET_ORDER);
        foreach ($attributes as $attribute) {
            $pattern .= '(?=[^>]*\s'.preg_quote($attribute[1], '~').(isset($attribute[2]) ? '='.preg_quote($attribute[2], '~') : '(?=[\s=>])').')';
        }
        $pattern .= '[^>]*>';
    }

    return '~'.$pattern.'~s';
}

expect()->extend('toContainMarkup', function (string $fragment) {
    Assert::assertMatchesRegularExpression(htmlPattern($fragment), (string) $this->value);

    return $this;
});

TestView::macro('assertSeeMarkup', function (string $fragment) {
    expect((string) $this)->toContainMarkup($fragment);

    return $this;
});

TestView::macro('assertSeeMarkupInOrder', function (array $fragments) {
    $remaining = (string) $this;
    foreach ($fragments as $fragment) {
        $pattern = htmlPattern($fragment);
        Assert::assertSame(1, preg_match($pattern, $remaining, $match, PREG_OFFSET_CAPTURE), 'Missing ordered markup: '.$fragment);
        $remaining = substr($remaining, $match[0][1] + strlen($match[0][0]));
    }

    return $this;
});

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
