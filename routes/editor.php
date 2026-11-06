<?php

use Esoboy\Ui\Http\Controllers\EditorUploadController;
use Illuminate\Support\Facades\Route;

// web: the session and CSRF check a form's post has. Who may upload, how often and how
// much is the app's uploader's to say.
Route::post('esoboy-ui/editor/upload', EditorUploadController::class)
    ->middleware('web')
    ->name('esoboy-ui.editor.upload');
