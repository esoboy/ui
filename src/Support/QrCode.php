<?php

namespace Esoboy\Ui\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

final class QrCode
{
    /** @return array{size: int, path: string} */
    public static function matrix(string $value): array
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        $path = '';
        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $path .= 'M'.($x + 4).' '.($y + 4).'h1v1h-1z';
                }
            }
        }

        return ['size' => $matrix->getWidth() + 8, 'path' => $path];
    }
}
