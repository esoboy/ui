<?php

namespace Esoboy\Ui\Contracts;

use Esoboy\Ui\Support\EditorUpload;

/**
 * What stores the Editor's uploads, the app's (config ui.editor.uploader): files dropped
 * or pasted, images pasted as data or from other sites, each as a file. Who may
 * upload and how often is the app's to decide before the editor retrieves any file.
 */
interface EditorUploader
{
    /**
     * Authorize this request before any remote fetch. Throw AuthorizationException to deny it.
     *
     * @param  array<string, mixed>  $context
     */
    public function authorize(string $source, ?string $url, array $context): void;

    /**
     * Stores the file and says what the content shows of it: ['url' => …] with any of
     * name, mime, size (bytes), alt, width, height and poster; null when it won't.
     *
     * @return array{url: string, name?: string|null, mime?: string|null, size?: int|null, alt?: string|null, width?: int|null, height?: int|null, poster?: string|null}|null
     */
    public function upload(EditorUpload $upload): ?array;
}
