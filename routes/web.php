<?php

use App\Models\Tournament;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    $tournaments = Tournament::orderBy('TournamentDate', 'desc')->get();
    return view('home', compact('tournaments'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/autowrestle.php';
