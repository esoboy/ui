<?php

use Esoboy\Ui\Support\Assets;
use Illuminate\Support\Facades\Route;

Route::get('esoboy/ui/{file}/{version}', Assets::class)->name('esoboy.ui.asset');
