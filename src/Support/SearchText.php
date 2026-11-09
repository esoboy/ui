<?php

namespace Esoboy\Ui\Support;

final class SearchText
{
    public static function matches(string $query, string $text): bool
    {
        $query = preg_replace('/\s+/u', '', mb_strtolower($query)) ?? '';
        $text = mb_strtolower($text);
        $offset = 0;
        foreach (mb_str_split($query) as $character) {
            $position = mb_strpos($text, $character, $offset);
            if ($position === false) {
                return false;
            }
            $offset = $position + 1;
        }

        return true;
    }
}
