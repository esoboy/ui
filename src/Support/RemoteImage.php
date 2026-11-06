<?php

namespace Esoboy\Ui\Support;

use finfo;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\StreamWrapper;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use LengthException;
use Psr\Http\Message\ResponseInterface;

/**
 * An image on another site as a file, for the uploader: what the server does when the
 * browser can't read it (its site doesn't share it with pages, CORS). Only from public
 * addresses, never the server's own network (localhost, 10.x, 169.254.x, …), through
 * redirects too, connecting to the address checked; no larger than PHP takes uploads;
 * a picture (EditorUploads::IMAGES), whatever it was called.
 */
final class RemoteImage
{
    private const REDIRECTS = 3;

    public static function download(string $url): ?UploadedFile
    {
        if (! extension_loaded('curl') || ($path = tempnam(sys_get_temp_dir(), 'ui-image-')) === false) {
            return null;
        }

        $max = (int) min(UploadedFile::getMaxFilesize(), 50 * 1024 * 1024);
        $file = null;

        try {
            for ($hop = 0; $hop <= self::REDIRECTS; $hop++) {
                $target = self::target($url);

                if ($target === null) {
                    return null;
                }

                $response = self::get($url, $path, $max, ...$target);

                if ($response?->redirect()) {
                    $location = $response->header('Location');
                    if ($location === '' || ! self::validUrlCharacters($location)) {
                        return null;
                    }
                    $next = rescue(fn () => (string) UriResolver::resolve(new Uri($url), new Uri($location)), report: false);
                    if ($next === null) {
                        return null;
                    }
                    $url = $next;

                    continue;
                }

                if ($response?->successful() && (int) $response->header('Content-Length') <= $max) {
                    $file = self::file($path, $url, $max);
                }

                return $file;
            }
        } finally {
            if ($file === null) {
                @unlink($path);
            }
        }

        return null;
    }

    /**
     * Where an address connects: its host, port and the public IP it resolves to; null
     * for one not on http(s), or resolving (any of its IPs) to a private or reserved one.
     *
     * @return array{string, int, string}|null
     */
    private static function target(string $url): ?array
    {
        if (! self::validUrlCharacters($url)) {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = trim($parts['host'] ?? '', '[]');

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || ($parts['port'] ?? 1) < 1) {
            return null;
        }

        $literal = filter_var($host, FILTER_VALIDATE_IP) !== false;
        if (! $literal && (! preg_match('/^[a-z0-9.-]+$/iD', $host) || preg_match('/(?:^|\.)(?:\d+|0x[a-f\d]+)\.?$/iD', $host))) {
            return null;
        }

        $ips = $literal ? [$host] : self::resolve($host);
        $public = array_filter($ips, fn (string $ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) !== false
            && (! str_contains($ip, ':') || (preg_match('/^[23]/', $ip) && ! str_starts_with($ip, '2002:'))));

        if ($ips === [] || count($public) < count($ips)) {
            return null;
        }

        return [$host, $parts['port'] ?? ($scheme === 'https' ? 443 : 80), $ips[0]];
    }

    /**
     * @return list<string>
     */
    private static function resolve(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        $ips = array_values(array_filter([...array_column($records, 'ip'), ...array_column($records, 'ipv6')], 'is_string'));

        return $ips !== [] ? $ips : (gethostbynamel($host) ?: []);
    }

    private static function validUrlCharacters(string $url): bool
    {
        return ! preg_match('~[\x00-\x20\x7f\\\\]~', $url);
    }

    private static function get(string $url, string $path, int $max, string $host, int $port, string $ip): ?Response
    {
        $address = str_contains($ip, ':') ? "[{$ip}]" : $ip;
        $curl = $host === $ip ? [] : [CURLOPT_RESOLVE => ["{$host}:{$port}:{$address}"]];

        return rescue(function () use ($url, $path, $max, $curl): ?Response {
            $stream = Utils::streamFor(Utils::tryFopen($path, 'w+b'));
            $limitExceeded = false;
            $sink = StreamWrapper::getResource(FnStream::decorate($stream, [
                'write' => static function (string $bytes) use ($stream, $max, &$limitExceeded): int {
                    if ($stream->tell() + strlen($bytes) > $max) {
                        $limitExceeded = true;

                        return 0;
                    }

                    return $stream->write($bytes);
                },
            ]));

            try {
                $response = Http::withOptions([
                    'allow_redirects' => false,
                    'proxy' => '',
                    'decode_content' => false,
                    'sink' => $sink,
                    'curl' => $curl,
                    'on_headers' => static function (ResponseInterface $response) use ($max): void {
                        if ((int) $response->getHeaderLine('Content-Length') > $max) {
                            throw new LengthException('Remote image exceeds the download limit.');
                        }
                    },
                ])->setHandler(new CurlHandler)->connectTimeout(5)->timeout(15)->accept('image/*')->get($url);

                return $limitExceeded ? null : $response;
            } finally {
                fclose($sink);
                $stream->close();
            }
        }, report: false);
    }

    // The body, up to the most it takes, as a file named after the address.
    private static function file(string $path, string $url, int $max): ?UploadedFile
    {
        clearstatcache(true, $path);
        $size = filesize($path);
        $mime = $size > 0 && $size <= $max ? (new finfo(FILEINFO_MIME_TYPE))->file($path) : null;

        if (! in_array($mime, EditorUploads::IMAGES, true)) {
            return null;
        }

        $name = pathinfo(rawurldecode((string) parse_url($url, PHP_URL_PATH)), PATHINFO_FILENAME) ?: 'image';
        $extension = ['image/jpeg' => 'jpg'][$mime] ?? explode('/', $mime)[1];

        return new UploadedFile($path, "{$name}.{$extension}", $mime, null, true);
    }
}
