<?php

use App\Http\Controllers\Api\Volunteering\RequirementsController;
use App\Http\Controllers\Api\Volunteering\ShiftRequirementsController;
use App\Http\Controllers\Api\Volunteering\ShiftsController;
use App\Http\Controllers\Api\Volunteering\ShiftSignupsController;
use App\Http\Controllers\Api\Volunteering\ShiftTypesController;
use App\Http\Controllers\Api\Volunteering\TeamsController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Volunteer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['token.refresh'])->as('api.')->group(function () {
    Orion::hasManyResource('events', 'teams', TeamsController::class)->except(['associate', 'dissociate', 'restore']);
    Orion::hasManyResource('teams', 'shift-types', ShiftTypesController::class)->except(['associate', 'dissociate']);
    Orion::hasManyResource('shift-types', 'shifts', ShiftsController::class)->except(['associate', 'dissociate']);

    Orion::resource('shift-requirements', RequirementsController::class)->withSoftDeletes();

    Orion::belongsToManyResource('shift-types', 'requirements', ShiftRequirementsController::class);

    Orion::belongsToManyResource('shifts', 'signups', ShiftSignupsController::class)->only(['search', 'index']);
    Route::post('/shifts/{shift}/signup', [ShiftSignupsController::class, 'signupAction'])->name('api.shifts.signups.signup');
    Route::post('/shifts/{shift}/cancel', [ShiftSignupsController::class, 'cancelAction'])->name('api.shifts.signups.cancel');
});
