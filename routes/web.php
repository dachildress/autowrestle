<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Mail test (only when APP_DEBUG=true – remove or protect in production)
Route::get('/test-mail', function () {
    if (!config('app.debug')) {
        abort(404);
    }
    $recipient = request('to', config('mail.from.address'));
    try {
        Mail::raw('AutoWrestle test email. If you see this, mail is working.', function ($message) use ($recipient) {
            $message->to($recipient)->subject('AutoWrestle mail test');
        });
        return response()->view('mail-test-result', ['success' => true, 'message' => 'Mail sent successfully to ' . $recipient], 200);
    } catch (\Throwable $e) {
        return response()->view('mail-test-result', [
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->name('test-mail');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/autowrestle.php';
