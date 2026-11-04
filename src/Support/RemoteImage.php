<?php

namespace Esoboy\Ui\Support;

use finfo;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

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
        $max = (int) min(UploadedFile::getMaxFilesize(), 50 * 1024 * 1024);

        for ($hop = 0; $hop <= self::REDIRECTS; $hop++) {
            $target = self::target($url);

            if ($target === null) {
                return null;
            }

            $response = self::get($url, ...$target);

            if ($response?->redirect()) {
                $url = self::follow($url, $response->header('Location'));

                continue;
            }

            if (! $response?->successful() || (int) $response->header('Content-Length') > $max) {
                return null;
            }

            return self::file($response, $url, $max);
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
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = trim($parts['host'] ?? '', '[]');

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user'])) {
            return null;
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) !== false ? [$host] : self::resolve($host);
        // Global ones only: not private, reserved, shared (100.64/10, a cloud's metadata), ….
        $public = array_filter($ips, fn (string $ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) === $ip);

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

    private static function get(string $url, string $host, int $port, string $ip): ?Response
    {
        $address = str_contains($ip, ':') ? "[{$ip}]" : $ip;

        return rescue(fn () => Http::withOptions([
            'allow_redirects' => false,
            'stream' => true,
            // The IP checked is the one connected to, not one the name resolves to next.
            'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$address}"]],
        ])->connectTimeout(5)->timeout(15)->accept('image/*')->get($url), report: false);
    }

    // A redirect's address, against the one redirecting.
    private static function follow(string $from, ?string $location): string
    {
        $location = trim((string) $location);
        $parts = parse_url($from);
        $origin = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').(isset($parts['port']) ? ":{$parts['port']}" : '');

        return match (true) {
            preg_match('~^[a-z][a-z\d+.-]*:~i', $location) === 1 => $location,
            str_starts_with($location, '//') => ($parts['scheme'] ?? 'https').':'.$location,
            str_starts_with($location, '/') => $origin.$location,
            default => $origin.preg_replace('~[^/]*$~', '', $parts['path'] ?? '/').$location,
        };
    }

    // The body, up to the most it takes, as a file named after the address.
    private static function file(Response $response, string $url, int $max): ?UploadedFile
    {
        $body = $response->toPsrResponse()->getBody();
        $path = (string) tempnam(sys_get_temp_dir(), 'ui-image-');
        $handle = fopen($path, 'wb');
        $size = 0;

        while ($handle && ! $body->eof() && $size <= $max) {
            $size += (int) fwrite($handle, $body->read(65536));
        }

        if ($handle) {
            fclose($handle);
        }

        $mime = $size > 0 && $size <= $max ? (new finfo(FILEINFO_MIME_TYPE))->file($path) : null;

        if (! in_array($mime, EditorUploads::IMAGES, true)) {
            @unlink($path);

            return null;
        }

        $name = pathinfo(rawurldecode((string) parse_url($url, PHP_URL_PATH)), PATHINFO_FILENAME) ?: 'image';
        $extension = ['image/jpeg' => 'jpg'][$mime] ?? explode('/', $mime)[1];

        return new UploadedFile($path, "{$name}.{$extension}", $mime, null, true);
    }
}
