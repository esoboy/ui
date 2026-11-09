<?php

use Esoboy\Ui\Support\RemoteImage;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

it('rejects private, invalid and non-image destinations when importing a URL', function (string $url, int $requests) {
    Http::fake([
        '93.184.216.34/inside' => Http::response('', 302, ['Location' => 'http://10.0.0.1/a.png']),
        '93.184.216.34/page' => Http::response('<html></html>', 200, ['Content-Type' => 'image/png']),
    ]);

    expect(RemoteImage::download($url))->toBeNull();
    Http::assertSentCount($requests);
})->with([
    'localhost' => ['http://127.0.0.1/a.png', 0],
    'cloud metadata' => ['http://169.254.169.254/latest/meta-data', 0],
    'shared network' => ['http://100.100.100.200/latest/meta-data', 0],
    'private network' => ['http://10.0.0.1/a.png', 0],
    'IPv6 loopback' => ['http://[::1]/a.png', 0],
    'IPv4 in IPv6' => ['http://[::ffff:127.0.0.1]/a.png', 0],
    'unresolvable' => ['http://unresolvable.invalid/a.png', 0],
    'file' => ['file:///etc/passwd', 0],
    'credentials' => ['http://user:secret@93.184.216.34/a.png', 0],
    'private redirect' => ['http://93.184.216.34/inside', 1],
    'non-image' => ['http://93.184.216.34/page', 1],
]);

it('fetches through cURL and revalidates each real redirect before connecting', function () {
    $listener = stream_socket_server('tcp://127.0.0.1:0');
    expect($listener)->toBeResource();
    $address = stream_socket_get_name($listener, false);
    fclose($listener);
    $port = parse_url('tcp://'.$address, PHP_URL_PORT);

    $server = new Process([PHP_BINARY, '-S', $address, __DIR__.'/../Fixtures/remote-image-server.php']);
    $server->start();

    try {
        $server->waitUntil(fn (string $type, string $output) => str_contains($output, 'started'));

        // Keep the production URL checks and handler, redirecting the socket to a local fixture.
        Http::globalOptions(['curl' => [CURLOPT_CONNECT_TO => ["93.184.216.34:{$port}:127.0.0.1:{$port}"]]]);
        $base = "http://93.184.216.34:{$port}";
        $image = RemoteImage::download($base.'/redirect');
        try {
            expect($image?->getClientOriginalName())->toBe('cat.png')
                ->and($image?->getMimeType())->toBe('image/png')
                ->and(RemoteImage::download($base.'/inside'))->toBeNull()
                ->and(RemoteImage::download($base.'/large'))->toBeNull();
        } finally {
            if ($image !== null) {
                unlink($image->getPathname());
            }
        }
    } finally {
        $server->stop();
    }
})->skip(! extension_loaded('curl') || ! function_exists('proc_open'), 'Requires PHP cURL and a local HTTP server.');
