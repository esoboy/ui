<?php

/*
 * QR Code: bacon's encoding, drawn as one SVG path.
 */

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Esoboy\Ui\Support\QrCode;

/**
 * The dark modules a path draws, as "x,y" keys.
 *
 * @return list<string>
 */
function qrModules(string $path): array
{
    preg_match_all('/M(\d+) (\d+)h(\d+)v1h-\3z/', $path, $runs, PREG_SET_ORDER);

    $modules = [];

    foreach ($runs as [, $x, $y, $width]) {
        for ($i = 0; $i < $width; $i++) {
            $modules[] = ($x + $i).','.$y;
        }
    }

    return $modules;
}

/**
 * The dark modules bacon encodes, as "x,y" keys.
 *
 * @return list<string>
 */
function baconModules(string $value, string $level): array
{
    $matrix = Encoder::encode($value, ErrorCorrectionLevel::valueOf($level), 'UTF-8', null, false)->getMatrix();
    $modules = [];

    for ($y = 0; $y < $matrix->getHeight(); $y++) {
        for ($x = 0; $x < $matrix->getWidth(); $x++) {
            if ($matrix->get($x, $y) === 1) {
                $modules[] = $x.','.$y;
            }
        }
    }

    return $modules;
}

it('draws exactly the modules bacon encodes', function (string $value, string $level) {
    $code = QrCode::draw($value, $level);

    expect(qrModules($code['path']))->toBe(baconModules($value, $level))
        ->and($code['path'])->toMatch('/^(M\d+ \d+h\d+v1h-\d+z)+$/');
})->with([
    ['https://esoboy.com/orders/1024', 'M'],
    ['12345678901234567890', 'L'],
    ['扫码登录：esoboy 管理后台', 'H'],
]);

it('renders the code as a labelled image', function () {
    app()->setLocale('zh_CN');
    $code = QrCode::draw('https://esoboy.com');

    expect((string) $this->blade('<x-ui::qr-code value="https://esoboy.com" :size="200" class="rounded-none" />'))
        ->toContain('<div role="img" aria-label="二维码" data-slot="qr-code" class="rounded-none">')
        ->toContain('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$code['modules'].' '.$code['modules'].'" width="200" height="200" shape-rendering="crispEdges" aria-hidden="true"><path fill="currentColor" d="'.$code['path'].'"/></svg>')
        ->not->toContain('qr-code-icon');
});

it('raises the error level to H under an icon, unless one is given', function () {
    $withIcon = (string) $this->blade('<x-ui::qr-code value="https://esoboy.com" icon="/logo.svg" :icon-size="32" />');
    $chosen = (string) $this->blade('<x-ui::qr-code value="https://esoboy.com" icon="/logo.svg" level="Q" />');

    expect($withIcon)
        ->toContain('d="'.QrCode::draw('https://esoboy.com', 'H')['path'].'"')
        ->toContain('<img data-slot="qr-code-icon" src="/logo.svg" alt="" width="32" height="32">')
        ->and($chosen)->toContain('d="'.QrCode::draw('https://esoboy.com', 'Q')['path'].'"');
});

it('fails loudly for an unknown error level', function () {
    QrCode::draw('esoboy', 'X');
})->throws(Exception::class);
