<?php

namespace Esoboy\Ui\Support;

use Esoboy\Ui\Contracts\EditorUploader;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

/**
 * The Editor's uploads as config ui.editor says: the app's uploader, the images it
 * uploads without anyone asking, and the hosts whose images are the app's own.
 */
final class EditorUploads
{
    /**
     * Images pasted as data or taken from other sites: pictures (not SVG, which holds
     * markup).
     */
    public const IMAGES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/bmp', 'image/avif'];

    public static function enabled(): bool
    {
        return filled(config('ui.editor.uploader'));
    }

    public static function uploader(): ?EditorUploader
    {
        $class = config('ui.editor.uploader');

        if (! is_string($class) || $class === '') {
            return null;
        }

        $uploader = app($class);

        if (! $uploader instanceof EditorUploader) {
            throw new InvalidArgumentException("[{$class}] is not an uploader; it must implement ".EditorUploader::class.'.');
        }

        return $uploader;
    }

    /**
     * The images uploaded as they come in: external, data, both or neither.
     *
     * @return list<'external'|'data'>
     */
    public static function auto(): array
    {
        $auto = (array) config('ui.editor.auto_upload', []);

        return self::enabled() ? array_values(array_filter(['external', 'data'], fn (string $kind) => (bool) ($auto[$kind] ?? false))) : [];
    }

    /**
     * The app's hosts, as a browser writes a URL's host (with its port, unless the
     * scheme's own): config's, APP_URL's, ASSET_URL's and the filesystem disks'.
     *
     * @return list<string>
     */
    public static function hosts(): array
    {
        $urls = [config('app.url'), config('app.asset_url'), ...array_column((array) config('filesystems.disks', []), 'url')];

        return array_values(array_unique(array_filter([
            // Config's as hosts (cdn.example.com, *.example.com) or URLs; the rest URLs.
            ...array_map(fn (mixed $host) => is_string($host) ? (str_contains($host, '://') ? self::host($host) : strtolower(trim($host))) : null, (array) config('ui.editor.hosts', [])),
            ...array_map(fn (mixed $url) => is_string($url) ? self::host($url) : null, $urls),
        ])));
    }

    /**
     * The page's context for the uploader, sealed so the browser can't change it.
     *
     * @param  array<string, mixed>  $context
     */
    public static function seal(array $context): string
    {
        return Crypt::encryptString((string) json_encode($context));
    }

    /**
     * A sealed context back, or none for none; one that was changed throws.
     *
     * @return array<string, mixed>
     */
    public static function open(?string $sealed): array
    {
        if ($sealed === null || $sealed === '') {
            return [];
        }

        $context = json_decode(Crypt::decryptString($sealed), true);

        return is_array($context) ? $context : throw new DecryptException('The upload context is not one.');
    }

    // A URL's host; none for a relative one (a disk's /storage).
    private static function host(string $url): ?string
    {
        $parts = parse_url(trim($url));

        if (! isset($parts['host'])) {
            return null;
        }

        $port = $parts['port'] ?? null;
        $own = ['http' => 80, 'https' => 443][strtolower($parts['scheme'] ?? '')] ?? null;

        return strtolower($parts['host']).($port !== null && $port !== $own ? ":{$port}" : '');
    }
}
