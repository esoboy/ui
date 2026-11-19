<?php

namespace Esoboy\Ui\Support;

final class Locale
{
    public static function normalize(string $locale): string
    {
        // Accept language tags such as zh-Hant-TW and POSIX names such as zh_CN.UTF-8.
        return preg_match('/\A(?:cn|zh(?:[-_][a-z0-9]{1,8})*)(?:\.[a-z0-9-]+)?\z/i', $locale) ? 'cn' : $locale;
    }
}
