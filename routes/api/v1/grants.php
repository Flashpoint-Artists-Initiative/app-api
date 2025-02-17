<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Grants\ArtProjectsController;
use App\Http\Controllers\Api\Grants\ProjectImagesController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Grants Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->as('api.')->group(function () {
    Orion::resource('art-projects', ArtProjectsController::class)->withSoftDeletes();
    Route::post('/art-projects/{art_project}/vote', [ArtProjectsController::class, 'voteAction'])->name('art-projects.vote');

    Orion::hasManyResource('art-projects', 'images', ProjectImagesController::class)->except(['associate', 'dissociate', 'search']);
});
