<?php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This project serves the "Wihima" site extracted from wihima.zip.
| The site lives under `public/hub/` as a standalone PHP app.
| We keep Laravel's front controller intact, but the homepage redirects
| to the standalone app entrypoint.
|
*/

Route::get('/', function () {
    return redirect('/hub/index.php');
})->name('home');