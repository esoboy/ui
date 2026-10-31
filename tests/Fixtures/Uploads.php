<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Esoboy\Ui\Contracts\EditorUploader;
use Esoboy\Ui\Support\EditorUpload;
use Illuminate\Validation\ValidationException;

/**
 * An app's uploader, as the tests have it: keeps what it was given, answers with an
 * address by the file's name; a file named refuse.png it refuses, empty.png it won't.
 */
final class Uploads implements EditorUploader
{
    /** @var list<EditorUpload> */
    public static array $uploads = [];

    public function upload(EditorUpload $upload): ?array
    {
        self::$uploads[] = $upload;

        $name = $upload->file->getClientOriginalName();

        if ($name === 'refuse.png') {
            throw ValidationException::withMessages(['file' => 'Not this one.']);
        }

        return $name === 'empty.png' ? null : ['url' => "/uploads/{$name}", 'name' => $name, 'mime' => $upload->file->getMimeType(), 'size' => $upload->file->getSize()];
    }
}
