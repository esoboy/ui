<?php

namespace Esoboy\Ui\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class Assets
{
    public const DIRECTORY = __DIR__.'/../../resources/dist';

    public const PUBLIC_DIRECTORY = 'vendor/esoboy/ui';

    private const TYPES = [
        'ui.js' => 'text/javascript; charset=utf-8',
        'editor.js' => 'text/javascript; charset=utf-8',
        'editor.css' => 'text/css; charset=utf-8',
    ];

    public static function url(string $file): string
    {
        $version = self::version($file);
        $relativePath = self::PUBLIC_DIRECTORY.'/'.$file;
        $published = public_path($relativePath);

        if (is_file($published) && hash_file('xxh128', $published) === $version) {
            return asset($relativePath).'?v='.$version;
        }

        return route('esoboy.ui.asset', ['file' => $file, 'version' => $version]);
    }

    public function __invoke(Request $request, string $file, string $version): BinaryFileResponse
    {
        abort_unless($version === self::version($file), 404);

        $response = response()->file(self::DIRECTORY.'/'.$file, [
            'Content-Type' => self::TYPES[$file],
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ])->setEtag($version);
        $response->isNotModified($request);

        return $response;
    }

    private static function version(string $file): string
    {
        abort_unless(isset(self::TYPES[$file]), 404);
        static $versions = [];

        return $versions[$file] ??= (string) hash_file('xxh128', self::DIRECTORY.'/'.$file);
    }
}
