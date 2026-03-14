<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AutoWrestle manage routes (tournament management - auth required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('tournaments/manage')->name('manage.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/{id}/view', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'viewSummary'])->name('view.summary')->where('id', '[0-9]+');
    Route::get('/{id}/edit', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'edit'])->name('tournaments.edit')->where('id', '[0-9]+');
    Route::post('/{id}/edit', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'update'])->name('tournaments.update')->where('id', '[0-9]+');
    Route::get('/{id}', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'show'])->name('tournaments.show')->where('id', '[0-9]+');

    Route::get('scorers', [\App\Http\Controllers\Manage\ManageScorerController::class, 'index'])->name('scorers.index');
    Route::get('scorers/create', [\App\Http\Controllers\Manage\ManageScorerController::class, 'create'])->name('scorers.create');
    Route::post('scorers', [\App\Http\Controllers\Manage\ManageScorerController::class, 'store'])->name('scorers.store');
    Route::get('scorers/{id}/edit', [\App\Http\Controllers\Manage\ManageScorerController::class, 'edit'])->name('scorers.edit')->where('id', '[0-9]+');
    Route::patch('scorers/{id}', [\App\Http\Controllers\Manage\ManageScorerController::class, 'update'])->name('scorers.update')->where('id', '[0-9]+');
    Route::delete('scorers/{id}', [\App\Http\Controllers\Manage\ManageScorerController::class, 'destroy'])->name('scorers.destroy')->where('id', '[0-9]+');

    Route::prefix('{tid}')->where(['tid' => '[0-9]+'])->group(function () {
        Route::get('divisions', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'index'])->name('divisions.index');
        Route::get('divisions/create', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'create'])->name('divisions.create');
        Route::post('divisions', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'store'])->name('divisions.store');
        Route::get('divisions/{did}', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'show'])->name('divisions.show')->where('did', '[0-9]+');
        Route::get('divisions/{did}/edit', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'edit'])->name('divisions.edit')->where('did', '[0-9]+');
        Route::post('divisions/{did}', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'update'])->name('divisions.update')->where('did', '[0-9]+');
        Route::get('divisions/{did}/delete', [\App\Http\Controllers\Manage\ManageDivisionController::class, 'destroy'])->name('divisions.destroy')->where('did', '[0-9]+');

        Route::get('divisions/{did}/period-settings', [\App\Http\Controllers\Manage\ManageDivisionPeriodController::class, 'index'])->name('divisions.period-settings.index')->where('did', '[0-9]+');
        Route::post('divisions/{did}/period-settings', [\App\Http\Controllers\Manage\ManageDivisionPeriodController::class, 'store'])->name('divisions.period-settings.store')->where('did', '[0-9]+');
        Route::post('divisions/{did}/period-settings/defaults', [\App\Http\Controllers\Manage\ManageDivisionPeriodController::class, 'defaults'])->name('divisions.period-settings.defaults')->where('did', '[0-9]+');

        Route::get('divisions/{did}/groups/create', [\App\Http\Controllers\Manage\ManageGroupController::class, 'create'])->name('groups.create')->where('did', '[0-9]+');
        Route::post('divisions/{did}/groups', [\App\Http\Controllers\Manage\ManageGroupController::class, 'store'])->name('groups.store')->where('did', '[0-9]+');
        Route::get('divisions/{did}/groups/{gid}/edit', [\App\Http\Controllers\Manage\ManageGroupController::class, 'edit'])->name('groups.edit')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);
        Route::post('divisions/{did}/groups/{gid}', [\App\Http\Controllers\Manage\ManageGroupController::class, 'update'])->name('groups.update')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);
        Route::get('divisions/{did}/groups/{gid}/delete', [\App\Http\Controllers\Manage\ManageGroupController::class, 'destroy'])->name('groups.destroy')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);

        Route::get('bracket/{did}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'create'])->name('brackets.create')->where('did', '[0-9]+');
        Route::get('bracket/show/{gid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'show'])->name('brackets.show')->where('gid', '[0-9]+');
        Route::get('unbracket/{did}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'unbracket'])->name('brackets.unbracket')->where('did', '[0-9]+');
        Route::get('deletewrestler/{wid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'deleteWrestler'])->name('brackets.deleteWrestler')->where('wid', '[0-9]+');
        Route::get('movewrestler/{wid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'moveWrestlerForm'])->name('brackets.moveWrestlerForm')->where('wid', '[0-9]+');
        Route::post('movewrestlertogroup/{wid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'moveWrestlerToGroup'])->name('brackets.moveWrestlerToGroup')->where('wid', '[0-9]+');
        Route::post('movewrestler/{wid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'moveWrestler'])->name('brackets.moveWrestler')->where('wid', '[0-9]+');
        Route::post('movewrestlertolast/{wid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'moveWrestlerToLast'])->name('brackets.moveWrestlerToLast')->where('wid', '[0-9]+');

        Route::get('bout/{did}', [\App\Http\Controllers\Manage\ManageBoutController::class, 'create'])->name('bouts.create')->where('did', '[0-9]+');
        Route::get('unbout/{did}', [\App\Http\Controllers\Manage\ManageBoutController::class, 'unbout'])->name('bouts.unbout')->where('did', '[0-9]+');
        Route::get('bout/print/{did}/{rid?}', [\App\Http\Controllers\Manage\ManageBoutController::class, 'printBouts'])->name('bouts.print')->where('did', '[0-9]+')->defaults('rid', 0);
        Route::get('bout/printdiv', [\App\Http\Controllers\Manage\ManageBoutController::class, 'selectPrint'])->name('bouts.selectPrint');
        Route::get('mats', [\App\Http\Controllers\Manage\ManageMatController::class, 'index'])->name('mats.index');
        Route::get('mats/from/{mat}', [\App\Http\Controllers\Manage\ManageMatController::class, 'fromMat'])->name('mats.fromMat')->where('mat', '[0-9]+');
        Route::post('mats/move', [\App\Http\Controllers\Manage\ManageMatController::class, 'move'])->name('mats.move');

        Route::get('scansheet/print', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'printScanSheet'])->name('scansheet.print');

        Route::get('score', [\App\Http\Controllers\Manage\ManageScoreController::class, 'index'])->name('scoring.index');
        Route::post('score', [\App\Http\Controllers\Manage\ManageScoreController::class, 'show'])->name('scoring.show');
        Route::post('score/update', [\App\Http\Controllers\Manage\ManageScoreController::class, 'update'])->name('scoring.update');

        Route::get('projection', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'index'])->name('projection.index');
        Route::get('projection/create', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'create'])->name('projection.create');
        Route::post('projection', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'store'])->name('projection.store');
        Route::get('projection/{id}/edit', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'edit'])->name('projection.edit')->where('id', '[0-9]+');
        Route::patch('projection/{id}', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'update'])->name('projection.update')->where('id', '[0-9]+');
        Route::delete('projection/{id}', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'destroy'])->name('projection.destroy')->where('id', '[0-9]+');
        Route::get('projection/{id}/display', [\App\Http\Controllers\Manage\ManageProjectionController::class, 'display'])->name('projection.display')->where('id', '[0-9]+');

        Route::get('viewgroups', [\App\Http\Controllers\Manage\ViewGroupsController::class, 'index'])->name('viewgroups.index');
        Route::get('viewgroups/wrestler/{wid}/edit', [\App\Http\Controllers\Manage\ViewGroupsController::class, 'editWrestler'])->name('viewgroups.editWrestler')->where('wid', '[0-9]+');
        Route::put('viewgroups/wrestler/{wid}', [\App\Http\Controllers\Manage\ViewGroupsController::class, 'updateWrestler'])->name('viewgroups.updateWrestler')->where('wid', '[0-9]+');
        Route::get('viewgroups/{did}/{gid}', [\App\Http\Controllers\Manage\ViewGroupsController::class, 'show'])->name('viewgroups.show')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);

        Route::get('checkin', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'index'])->name('checkin.index');
        Route::get('checkin/clear-unchecked', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'clearUnchecked'])->name('checkin.clearUnchecked');
        Route::get('checkin/clear-unchecked/{did}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'clearUncheckedDivision'])->name('checkin.clearUncheckedDivision')->where('did', '[0-9]+');
        Route::get('checkin/update/{id}/{value}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'update'])->name('checkin.update')->where('value', '[01]');
        Route::get('checkin/print/{did}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'printDivision'])->name('checkin.print')->where('did', '[0-9]+');
        Route::get('checkin/{did}/{gid}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'show'])->name('checkin.show')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);
    });
});
