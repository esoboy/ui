<?php

namespace Esoboy\Ui\Http\Controllers;

use Esoboy\Ui\Assets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves dist/ files: precompressed when the browser accepts it, cached for a year
 * (URLs carry the file's version).
 */
final class AssetController
{
    private const TYPES = [
        'js' => 'text/javascript; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
    ];

    public function __invoke(Request $request, Assets $assets, string $file): BinaryFileResponse
    {
        $path = $assets->path($file) ?? abort(404);
        $encodings = $request->getEncodings();

        [$served, $encoding] = match (true) {
            in_array('br', $encodings, true) && is_file($path.'.br') => [$path.'.br', 'br'],
            in_array('gzip', $encodings, true) && is_file($path.'.gz') => [$path.'.gz', 'gzip'],
            default => [$path, null],
        };

        $response = new BinaryFileResponse($served, headers: array_filter([
            'Content-Type' => self::TYPES[pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream',
            'Content-Encoding' => $encoding,
            'Cache-Control' => 'max-age=31536000, immutable',
            'Vary' => 'Accept-Encoding',
        ]));

        $response->isNotModified($request);

        return $response;
    }
}
