<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class DrawingValue
{
    public const MAX_STROKES = 200;

    public const MAX_POINTS = 4096;

    /** @return array{x:float,y:float,width:float,height:float} */
    public static function crop(mixed $value): array
    {
        $value = $value ?? ['x' => 0.1, 'y' => 0.1, 'width' => 0.8, 'height' => 0.8];
        if (! is_array($value)) {
            throw new InvalidArgumentException(__('ui::errors.crop_fields'));
        }
        foreach (['x', 'y', 'width', 'height'] as $key) {
            if (! isset($value[$key]) || (! is_int($value[$key]) && ! is_float($value[$key])) || ! is_finite((float) $value[$key])) {
                throw new InvalidArgumentException(__('ui::errors.crop_coordinates'));
            }
        }

        return self::normalize($value['x'], $value['y'], $value['width'], $value['height']);
    }

    /** @return array{x:float,y:float,width:float,height:float} */
    private static function normalize(float $x, float $y, float $width, float $height): array
    {
        $width = max(.02, min(1, $width));
        $height = max(.02, min(1, $height));

        return ['x' => max(0, min(1 - $width, $x)), 'y' => max(0, min(1 - $height, $y)), 'width' => $width, 'height' => $height];
    }

    /** @return list<list<array{x:float,y:float}>> */
    public static function signature(mixed $value): array
    {
        $value = $value ?? [];
        if (! is_array($value) || ! array_is_list($value) || count($value) > self::MAX_STROKES) {
            throw new InvalidArgumentException(__('ui::errors.signature_strokes'));
        }

        return array_map(fn (mixed $points): array => self::points($points), $value);
    }

    /** @return list<array{x:float,y:float}> */
    private static function points(mixed $points): array
    {
        if (! is_array($points) || ! array_is_list($points) || count($points) < 1 || count($points) > self::MAX_POINTS) {
            throw new InvalidArgumentException(__('ui::errors.signature_points'));
        }
        $result = [];
        foreach ($points as $point) {
            if (! is_array($point) || ! isset($point['x'],$point['y']) || ! is_numeric($point['x']) || ! is_numeric($point['y'])) {
                throw new InvalidArgumentException(__('ui::errors.signature_coordinates'));
            }
            $x = (float) $point['x'];
            $y = (float) $point['y'];
            if (! is_finite($x + $y) || $x < 0 || $x > 1 || $y < 0 || $y > 1) {
                throw new InvalidArgumentException(__('ui::errors.signature_range'));
            }
            $result[] = ['x' => $x, 'y' => $y];
        }

        return $result;
    }
}
