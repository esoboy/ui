<?php

namespace Esoboy\Ui\Support;

/**
 * Watermark tiles: the text as an SVG the layer repeats as a CSS mask, so its color
 * comes from CSS (the theme's foreground) and no script draws it. Rows are staggered
 * by half a cell, as Ant Design's watermark is.
 */
final class Watermark
{
    // System fonts only: an SVG used as an image can't load the page's web fonts.
    private const FONT = 'system-ui, sans-serif';

    /**
     * @param  non-empty-list<string>  $lines
     * @param  array{int, int}  $gap
     * @return array{image: string, width: int, height: int}
     */
    public static function tile(array $lines, int $fontSize, int $rotate, array $gap): array
    {
        $lineHeight = $fontSize * 1.5;

        // mb_strwidth counts CJK and other wide characters twice; a narrow character is about 0.55em.
        $textWidth = max(array_map(fn (string $line) => mb_strwidth($line) * 0.55 * $fontSize, $lines));
        $width = (int) ceil($textWidth + $gap[0]);
        $height = (int) ceil(count($lines) * $lineHeight + $gap[1]);

        // One cell in the first row; the second row is shifted by half a cell, so it straddles the edges.
        $text = fn (float $x, float $y) => sprintf('<g transform="translate(%s %s) rotate(%d)">', $x, $y, $rotate)
            .'<text text-anchor="middle" dominant-baseline="central" font-size="'.$fontSize.'" font-family="'.self::FONT.'">'
            .implode('', array_map(
                fn (string $line, int $index) => sprintf('<tspan x="0" y="%s">%s</tspan>', ($index - (count($lines) - 1) / 2) * $lineHeight, htmlspecialchars($line, ENT_XML1)),
                $lines,
                array_keys($lines),
            ))
            .'</text></g>';

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">', $width, $height * 2)
            .$text($width / 2, $height / 2)
            .$text(0, $height * 1.5)
            .$text($width, $height * 1.5)
            .'</svg>';

        return ['image' => 'url("data:image/svg+xml,'.rawurlencode($svg).'")', 'width' => $width, 'height' => $height * 2];
    }
}
