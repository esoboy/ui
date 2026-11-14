<?php

namespace Esoboy\Ui\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * What File Upload shows of the files behind its wire:model: Livewire's temporary
 * uploads (or any UploadedFile), as name, size, image preview and removal key.
 */
final class UploadedFiles
{
    /**
     * @return list<array{name: string, size: string, url: ?string, filename: string}>
     */
    public static function of(mixed $value): array
    {
        $files = $value instanceof Collection ? $value->all() : (is_array($value) ? $value : [$value]);

        return array_values(array_map(fn (UploadedFile $file) => [
            'name' => $file->getClientOriginalName(),
            'size' => self::size((int) $file->getSize()),
            'url' => self::preview($file),
            'filename' => $file->getFilename(),
        ], array_filter($files, fn ($file) => $file instanceof UploadedFile)));
    }

    public static function size(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;
        $value = $bytes / (1024 ** $power);

        return ($power === 0 ? $bytes : rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.')).' '.$units[$power];
    }

    // Only images get a thumbnail; Livewire can also preview audio and video.
    private static function preview(UploadedFile $file): ?string
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/') || ! method_exists($file, 'temporaryUrl')) {
            return null;
        }

        try {
            return $file->temporaryUrl();
        } catch (\Throwable) {
            return null;
        }
    }
}
