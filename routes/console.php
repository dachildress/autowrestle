<?php

use App\Services\TournamentSeedDebugService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tournament:seed-debug', function () {
    $tid = (int) $this->ask('Tournament ID?', 1);
    $pwCount = (int) $this->ask('How many wrestlers for PW division?', 12);
    $jrCount = (int) $this->ask('How many wrestlers for JR division?', 8);

    if ($pwCount < 0 || $jrCount < 0) {
        $this->error('Counts must be 0 or more.');
        return 1;
    }

    $service = app(TournamentSeedDebugService::class);
    try {
        $result = $service->run($tid, $pwCount, $jrCount);
        $this->info("Created {$result['wrestlers']} wrestlers and {$result['tournament_wrestlers']} tournament entries (80% boys, 20% girls per division).");
        return 0;
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return 1;
    }
})->purpose('Seed a tournament with synthetic wrestlers for debugging (prompts for tournament id, PW count, JR count).');
