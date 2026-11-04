<?php

namespace Esoboy\Ui\Http\Controllers;

use Esoboy\Ui\Support\EditorUpload;
use Esoboy\Ui\Support\EditorUploads;
use Esoboy\Ui\Support\RemoteImage;
use finfo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * What the Editor uploads, one file at a time, handed to the app's uploader (config
 * ui.editor.uploader): { attachment } back, its answer. An image from another site comes
 * as a file when the browser could read it, else as its address, fetched here
 * (RemoteImage). Images in the content are pictures, however they came.
 */
final class EditorUploadController
{
    public function __invoke(Request $request): JsonResponse
    {
        $uploader = EditorUploads::uploader() ?? abort(404);

        $request->validate([
            'source' => ['required', Rule::in(EditorUpload::SOURCES)],
            'file' => ['nullable', 'file', 'required_unless:source,external'],
            'url' => ['nullable', 'string', 'max:4096', 'required_if:source,external'],
            'context' => ['nullable', 'string'],
        ]);

        try {
            $context = EditorUploads::open($request->input('context'));
        } catch (DecryptException) {
            abort(403);
        }

        $source = (string) $request->input('source');
        $url = $request->input('url');
        $file = $request->file('file');
        $downloaded = ! $file instanceof UploadedFile && is_string($url) ? RemoteImage::download($url) : null;
        $file = $file instanceof UploadedFile ? $file : $downloaded;

        try {
            // By what the file holds, whatever it was called.
            if (! $file || (in_array($source, ['data', 'external'], true) && ! in_array((new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()), EditorUploads::IMAGES, true))) {
                throw ValidationException::withMessages(['file' => __('ui::editor.image-unreadable')]);
            }

            $attachment = $uploader->upload(new EditorUpload($file, $source, is_string($url) ? $url : null, $context));
        } finally {
            if ($downloaded && is_file($downloaded->getPathname())) {
                @unlink($downloaded->getPathname());
            }
        }

        return new JsonResponse(['attachment' => is_string($attachment['url'] ?? null) ? $attachment : null]);
    }
}
