<?php

namespace Esoboy\Ui\Support;

final class EditorTranslations
{
    /** @return array<string, string> */
    public static function defaults(string $locale): array
    {
        static $catalogs = [];
        $language = Locale::normalize($locale) === 'cn' ? 'cn' : 'en';
        $path = dirname(__DIR__, 2).'/lang/'.$language.'/editor.json';

        return $catalogs[$language] ??= json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return array<string, string> */
    public static function overrides(): array
    {
        $fallback = __('ui::editor', [], app()->getFallbackLocale());
        $current = __('ui::editor');
        $messages = array_replace(is_array($fallback) ? $fallback : [], is_array($current) ? $current : []);

        // The editor bundle already contains both complete catalogs.
        return array_diff_assoc($messages, self::defaults(app()->getLocale()));
    }
}
