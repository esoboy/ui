<?php

namespace Esoboy\Ui\Contracts;

use Esoboy\Ui\Support\EditorUpload;

/**
 * What stores the Editor's uploads, the app's (config ui.editor.uploader): media picked
 * or dropped, images pasted as data or from other sites, each as a file. Who may
 * upload, how often and how much is the app's to decide here: throw an
 * AuthorizationException, a ValidationException (its message shown in the Editor), ….
 */
interface EditorUploader
{
    /**
     * Stores the file and says what the content shows of it: ['url' => …] with any of
     * name, mime, size (bytes), alt, width, height and poster; null when it won't.
     *
     * @return array{url: string, name?: string|null, mime?: string|null, size?: int|null, alt?: string|null, width?: int|null, height?: int|null, poster?: string|null}|null
     */
    public function upload(EditorUpload $upload): ?array;
}
