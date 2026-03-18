<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AutoWrestle manage routes (tournament management - auth required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('tournaments/manage')->name('manage.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/create', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/store', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'store'])->name('tournaments.store');
    Route::post('/{id}/approve', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'approve'])->name('tournaments.approve')->where('id', '[0-9]+');
    Route::get('/{id}/users', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'users'])->name('tournaments.users')->where('id', '[0-9]+');
    Route::post('/{id}/users', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'addUser'])->name('tournaments.users.store')->where('id', '[0-9]+');
    Route::post('/{id}/users/remove', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'removeUser'])->name('tournaments.users.remove')->where('id', '[0-9]+');
    Route::get('/{id}/view', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'viewSummary'])->name('view.summary')->where('id', '[0-9]+');
    Route::get('/{id}/edit', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'edit'])->name('tournaments.edit')->where('id', '[0-9]+');
    Route::post('/{id}/edit', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'update'])->name('tournaments.update')->where('id', '[0-9]+');
    Route::get('/{id}', [\App\Http\Controllers\Manage\ManageTournamentController::class, 'show'])->name('tournaments.show')->where('id', '[0-9]+');

    Route::get('content', [\App\Http\Controllers\Manage\SiteContentController::class, 'index'])->name('content.index');
    Route::get('content/{key}/edit', [\App\Http\Controllers\Manage\SiteContentController::class, 'edit'])->name('content.edit')->where('key', '[a-zA-Z0-9._-]+');
    Route::post('content/{key}', [\App\Http\Controllers\Manage\SiteContentController::class, 'update'])->name('content.update')->where('key', '[a-zA-Z0-9._-]+');

    Route::get('scorers', [\App\Http\Controllers\Manage\ManageScorerController::class, 'index'])->name('scorers.index');
    Route::get('scorers/create', [\App\Http\Controllers\Manage\ManageScorerController::class, 'create'])->name('scorers.create');
    Route::post('scorers', [\App\Http\Controllers\Manage\ManageScorerController::class, 'store'])->name('scorers.store');
    Route::get('scorers/{id}/edit', [\App\Http\Controllers\Manage\ManageScorerController::class, 'edit'])->name('scorers.edit')->where('id', '[0-9]+');
    Route::patch('scorers/{id}', [\App\Http\Controllers\Manage\ManageScorerController::class, 'update'])->name('scorers.update')->where('id', '[0-9]+');
    Route::delete('scorers/{id}', [\App\Http\Controllers\Manage\ManageScorerController::class, 'destroy'])->name('scorers.destroy')->where('id', '[0-9]+');

    Route::prefix('{tid}')->where(['tid' => '[0-9]+'])->group(function () {
        Route::get('reports', [\App\Http\Controllers\Manage\ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/completed', [\App\Http\Controllers\Manage\ReportsController::class, 'completed'])->name('reports.completed');
        Route::get('reports/groups', [\App\Http\Controllers\Manage\ReportsController::class, 'groups'])->name('reports.groups');
        Route::get('reports/groups/{gid}', [\App\Http\Controllers\Manage\ReportsController::class, 'groupShow'])->name('reports.groups.show')->where('gid', '[0-9]+');
        Route::get('reports/brackets', [\App\Http\Controllers\Manage\ReportsController::class, 'brackets'])->name('reports.brackets');
        Route::get('reports/brackets/{bid}', [\App\Http\Controllers\Manage\ReportsController::class, 'bracketShow'])->name('reports.brackets.show')->where('bid', '[0-9]+');
        Route::get('reports/wrestlers', [\App\Http\Controllers\Manage\ReportsController::class, 'wrestlers'])->name('reports.wrestlers');

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
        Route::get('bracket/show/{did}/{gid}', [\App\Http\Controllers\Manage\ManageBracketController::class, 'show'])->name('brackets.show')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);
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

        Route::get('mat-setup', [\App\Http\Controllers\Manage\TournamentMatController::class, 'index'])->name('mat-setup.index');
        Route::get('mat-setup/create', [\App\Http\Controllers\Manage\TournamentMatController::class, 'create'])->name('mat-setup.create');
        Route::post('mat-setup', [\App\Http\Controllers\Manage\TournamentMatController::class, 'store'])->name('mat-setup.store');
        Route::get('mat-setup/{mid}/edit', [\App\Http\Controllers\Manage\TournamentMatController::class, 'edit'])->name('mat-setup.edit')->where('mid', '[0-9]+');
        Route::post('mat-setup/{mid}', [\App\Http\Controllers\Manage\TournamentMatController::class, 'update'])->name('mat-setup.update')->where('mid', '[0-9]+');
        Route::get('mat-setup/{mid}/delete', [\App\Http\Controllers\Manage\TournamentMatController::class, 'destroy'])->name('mat-setup.destroy')->where('mid', '[0-9]+');

        Route::get('number-schemes', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'index'])->name('number-schemes.index');
        Route::get('number-schemes/create', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'create'])->name('number-schemes.create');
        Route::post('number-schemes', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'store'])->name('number-schemes.store');
        Route::get('number-schemes/{sid}/edit', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'edit'])->name('number-schemes.edit')->where('sid', '[0-9]+');
        Route::post('number-schemes/{sid}', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'update'])->name('number-schemes.update')->where('sid', '[0-9]+');
        Route::get('number-schemes/{sid}/delete', [\App\Http\Controllers\Manage\BoutNumberSchemeController::class, 'destroy'])->name('number-schemes.destroy')->where('sid', '[0-9]+');

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

        Route::get('import-settings', [\App\Http\Controllers\Manage\ImportSettingsController::class, 'index'])->name('import-settings.index');
        Route::get('import-settings/from/{sourceTid}', [\App\Http\Controllers\Manage\ImportSettingsController::class, 'from'])->name('import-settings.from')->where('sourceTid', '[0-9]+');
        Route::post('import-settings', [\App\Http\Controllers\Manage\ImportSettingsController::class, 'store'])->name('import-settings.store');

        Route::get('checklist', [\App\Http\Controllers\Manage\ChecklistController::class, 'index'])->name('checklist.index');
        Route::post('checklist/toggle', [\App\Http\Controllers\Manage\ChecklistController::class, 'toggle'])->name('checklist.toggle');

        Route::get('checkin', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'index'])->name('checkin.index');
        Route::get('checkin/clear-unchecked', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'clearUnchecked'])->name('checkin.clearUnchecked');
        Route::get('checkin/clear-unchecked/{did}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'clearUncheckedDivision'])->name('checkin.clearUncheckedDivision')->where('did', '[0-9]+');
        Route::get('checkin/update/{id}/{value}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'update'])->name('checkin.update')->where('value', '[01]');
        Route::get('checkin/print/{did}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'printDivision'])->name('checkin.print')->where('did', '[0-9]+');
        Route::get('checkin/{did}/{gid}', [\App\Http\Controllers\Manage\ManageCheckinController::class, 'show'])->name('checkin.show')->where(['did' => '[0-9]+', 'gid' => '[0-9]+']);
    });
});
