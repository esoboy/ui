<?php

use Esoboy\Ui\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

// No middleware: assets need no session, cookies or CSRF.
Route::get('esoboy-ui/{file}', AssetController::class)
    ->where('file', '.+')
    ->name('esoboy-ui.asset');
