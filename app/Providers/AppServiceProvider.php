<?php

namespace App\Providers;

use App\Models\Tournament;
use App\Services\BoutNumberSchemeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->shareManageNavData();
    }

    /**
     * When inside a tournament manage area (route has tid), share tournament and divisions
     * so the layout can render Tournament, View, Bracket, Bout, Print dropdowns.
     * Only share when the user is allowed to manage this tournament (admin or in tournament users).
     */
    protected function shareManageNavData(): void
    {
        View::composer('layouts.autowrestle', function ($view) {
            $tid = request()->route('tid') ?? request()->route('id');
            if ($tid === null || ! request()->user()) {
                return;
            }
            $tournament = Tournament::with([
                'divisions' => fn ($q) => $q->orderBy('id'),
                'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('id'),
            ])->find($tid);
            if (! $tournament) {
                return;
            }
            $user = request()->user();
            $canManageTournament = $user->isAdmin() || $tournament->users()->where('User_id', $user->id)->exists();
            if ($canManageTournament) {
                $schemeService = app(BoutNumberSchemeService::class);
                $divisionHasScheme = [];
                foreach ($tournament->divisions as $div) {
                    $divisionHasScheme[$div->id] = $schemeService->divisionHasScheme((int) $tournament->id, (int) $div->id);
                }
                $view->with(compact('tournament', 'divisionHasScheme'));
                $view->with('manageNav', true);
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
