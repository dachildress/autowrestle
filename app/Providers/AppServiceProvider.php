<?php

namespace App\Providers;

use App\Models\Tournament;
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
            if ($tournament) {
                $view->with(compact('tournament'));
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
