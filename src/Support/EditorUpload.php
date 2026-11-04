<?php

namespace Esoboy\Ui\Support;

use Illuminate\Http\UploadedFile;

/**
 * One file the Editor uploads, as its uploader gets it: the file, where it came from,
 * the address it was at (an image from another site), and the context the page gave
 * the Editor (upload-context, signed: as the page rendered it).
 */
final readonly class EditorUpload
{
    /**
     * Picked with a media button, dropped, pasted as a file, an image pasted as data
     * (a screenshot, Word's picture), an image on another site.
     */
    public const SOURCES = ['pick', 'drop', 'paste', 'data', 'external'];

    /**
     * @param  string  $source  one of SOURCES
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public UploadedFile $file,
        public string $source,
        public ?string $url = null,
        public array $context = [],
    ) {}
}
