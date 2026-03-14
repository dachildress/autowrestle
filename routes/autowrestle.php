<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AutoWrestle routes (Blade, server-rendered)
|--------------------------------------------------------------------------
*/

Route::get('/tournaments', fn () => redirect()->route('tournaments.list'))->name('tournaments.index');
Route::get('/tournaments/list', [\App\Http\Controllers\TournamentController::class, 'current'])->name('tournaments.list');
Route::get('/tournaments/mybout/search/{tid}', [\App\Http\Controllers\TournamentController::class, 'myboutSearch'])->name('mybouts.search')->where('tid', '[0-9]+');
Route::get('/tournaments/mybout/{tid}/{wid}', [\App\Http\Controllers\TournamentController::class, 'mybouts'])->name('mybouts.show')->where(['tid' => '[0-9]+', 'wid' => '[0-9]+']);
Route::get('/tournaments/{id}', [\App\Http\Controllers\TournamentController::class, 'show'])->name('tournaments.show')->where('id', '[0-9]+');

Route::middleware(['auth'])->group(function () {
    Route::get('/mat', [\App\Http\Controllers\MatDashboardController::class, 'index'])->name('mat.dashboard');
    Route::get('/mat/settings', [\App\Http\Controllers\MatSettingsController::class, 'index'])->name('mat.settings');
    Route::get('/mat/virtual', [\App\Http\Controllers\MatVirtualController::class, 'settings'])->name('mat.virtual');
    Route::get('/mat/virtual/display', [\App\Http\Controllers\MatVirtualController::class, 'display'])->name('mat.virtual.display');
    Route::get('/mat/virtual/current-state', [\App\Http\Controllers\MatVirtualController::class, 'currentState'])->name('mat.virtual.current-state');
    Route::get('/mat/bout/{boutId}', [\App\Http\Controllers\MatBoutController::class, 'show'])->name('mat.bout.show')->where('boutId', '[0-9]+');
    Route::get('/mat/bout/{boutId}/history', [\App\Http\Controllers\MatBoutController::class, 'history'])->name('mat.bout.history')->where('boutId', '[0-9]+');
    Route::get('/mat/bout/{boutId}/results', [\App\Http\Controllers\MatBoutController::class, 'results'])->name('mat.bout.results')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/results', [\App\Http\Controllers\MatBoutController::class, 'saveResult'])->name('mat.bout.results.save')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/reset', [\App\Http\Controllers\MatBoutController::class, 'reset'])->name('mat.bout.reset')->where('boutId', '[0-9]+');
    Route::get('/mat/bout/{boutId}/state', [\App\Http\Controllers\MatBoutController::class, 'state'])->name('mat.bout.state')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/clock', [\App\Http\Controllers\MatBoutController::class, 'clock'])->name('mat.bout.clock')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/period', [\App\Http\Controllers\MatBoutController::class, 'period'])->name('mat.bout.period')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/event', [\App\Http\Controllers\MatBoutController::class, 'event'])->name('mat.bout.event')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/comment', [\App\Http\Controllers\MatBoutController::class, 'comment'])->name('mat.bout.comment')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/timer', [\App\Http\Controllers\MatBoutController::class, 'timer'])->name('mat.bout.timer')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/display-swap', [\App\Http\Controllers\MatBoutController::class, 'displaySwap'])->name('mat.bout.display-swap')->where('boutId', '[0-9]+');
    Route::post('/mat/bout/{boutId}/complete', [\App\Http\Controllers\MatBoutController::class, 'complete'])->name('mat.bout.complete')->where('boutId', '[0-9]+');

    Route::get('/wrestlers', [\App\Http\Controllers\WrestlerController::class, 'index'])->name('wrestlers.index');
    Route::get('/wrestlers/create', [\App\Http\Controllers\WrestlerController::class, 'create'])->name('wrestlers.create');
    Route::post('/wrestlers', [\App\Http\Controllers\WrestlerController::class, 'store'])->name('wrestlers.store');
    Route::get('/wrestlers/{id}/edit', [\App\Http\Controllers\WrestlerController::class, 'edit'])->name('wrestlers.edit')->where('id', '[0-9]+');
    Route::post('/wrestlers/{id}', [\App\Http\Controllers\WrestlerController::class, 'update'])->name('wrestlers.update')->where('id', '[0-9]+');

    // Tournament registration
    Route::get('/tournaments/register/locked', [\App\Http\Controllers\RegistrationController::class, 'locked'])->name('tournaments.register.locked');
    Route::get('/tournaments/register/{id}', [\App\Http\Controllers\RegistrationController::class, 'register'])->name('tournaments.register')->where('id', '[0-9]+');
    Route::get('/tournaments/register/addwrestler/{wid}/{tid}', [\App\Http\Controllers\RegistrationController::class, 'addWrestlerForm'])->name('tournaments.register.add')->where(['wid' => '[0-9]+', 'tid' => '[0-9]+']);
    Route::post('/tournaments/register/insertwrestler/{wid}/{tid}', [\App\Http\Controllers\RegistrationController::class, 'insertWrestler'])->name('tournaments.register.insert')->where(['wid' => '[0-9]+', 'tid' => '[0-9]+']);
    Route::get('/tournaments/register/withdrawwrestler/{wid}/{tid}', [\App\Http\Controllers\RegistrationController::class, 'removeWrestler'])->name('tournaments.register.withdraw')->where(['wid' => '[0-9]+', 'tid' => '[0-9]+']);
});

require __DIR__.'/autowrestle_manage.php';
