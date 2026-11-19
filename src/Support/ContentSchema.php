<?php

namespace Esoboy\Ui\Support;

final class ContentSchema
{
    /** @return array{version: int, extensions: list<string>, nodes: array<string, list<string>>, marks: array<string, list<string>>, globalAttributes: list<string>, attributes: array<string, list<string>>, mediaDisplaySizes: list<string>, iframe: array{width: int, height: int, sandbox: string, playerOrigins: list<string>, attributes: array<string, string>}, styles: array<string, string>, maxLength: int} */
    public static function definition(): array
    {
        static $definition;

        return $definition ??= json_decode((string) file_get_contents(dirname(__DIR__, 2).'/resources/content/schema.json'), true, flags: JSON_THROW_ON_ERROR);
    }
}
