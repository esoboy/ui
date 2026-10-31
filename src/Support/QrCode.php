<?php

namespace Esoboy\Ui\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * QR codes drawn as one SVG path: bacon/bacon-qr-code encodes, each row's dark runs
 * become rectangles, and the path fills with currentColor so CSS colors it.
 */
final class QrCode
{
    /**
     * The path of the dark modules and how many modules the code is wide.
     *
     * @param  'L'|'M'|'Q'|'H'|string  $level
     * @return array{path: string, modules: int}
     */
    public static function draw(string $value, string $level = 'M'): array
    {
        // UTF-8 without an ECI header, as most generators write it and scanners (WeChat's too) read it.
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::valueOf(strtoupper($level)), 'UTF-8', null, false)->getMatrix();
        $size = $matrix->getWidth();
        $path = '';

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix->get($x, $y) !== 1) {
                    continue;
                }

                $start = $x;

                while ($x + 1 < $size && $matrix->get($x + 1, $y) === 1) {
                    $x++;
                }

                $path .= 'M'.$start.' '.$y.'h'.($x - $start + 1).'v1h-'.($x - $start + 1).'z';
            }
        }

        return ['path' => $path, 'modules' => $size];
    }
}
