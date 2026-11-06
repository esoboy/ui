<?php

namespace Esoboy\Ui\Support;

use Esoboy\Ui\Assets;

final class Emoji
{
    public static function score(string $label, string $keywords, string $query): int
    {
        return (str_contains(mb_strtolower($label), $query) ? 10 : 0)
            + count(array_filter(explode('|', mb_strtolower($keywords)), fn (string $keyword) => str_contains($keyword, $query)));
    }

    /** @return array<string, list<array{string, string, string, float}>> */
    public static function catalog(): array
    {
        $assets = app(Assets::class);
        $locale = strtolower(str_replace('_', '-', app()->getLocale()));
        $file = collect(["emoji/{$locale}.json", 'emoji/'.strtok($locale, '-').'.json', 'emoji/en.json'])->map(fn ($file) => $assets->path($file))->first(fn ($path) => $path !== null);

        $contents = $file ? file_get_contents($file) : false;

        return $contents !== false ? json_decode($contents, true, flags: JSON_THROW_ON_ERROR) : [];
    }
}
