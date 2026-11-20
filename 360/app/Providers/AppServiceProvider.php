<?php

namespace App\Providers;

use App\Models\ElectronicInvoice;
use App\Models\MonthlyPayment;
use App\Models\Section;
use App\Models\Student;
use App\Policies\ElectronicInvoicePolicy;
use App\Policies\MonthlyPaymentPolicy;
use App\Policies\SectionPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // HU-87: nunca permitir depuración visible en producción.
        if ($this->app->environment('production') && config('app.debug')) {
            config(['app.debug' => false]);
            Log::critical('APP_DEBUG=true en producción; se forzó APP_DEBUG=false.');
        }

        Password::defaults(function () {
            return Password::min(8);
        });

        // HU-87 Sprint 2: límites por usuario/IP para operaciones sensibles.
        RateLimiter::for('exportaciones', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(30)
            ->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('inscripcion', fn (Request $request) => Limit::perMinute(20)
            ->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('archivos', fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));

        // HU05: Directivas Blade para ocultar menús y botones según rol
        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Blade::if('hasanyrole', function (string $roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });

        // Gates para verificar permisos a módulos
        Gate::define('access-module', function ($user, string $modulo) {
            return $user->canAccess($modulo);
        });

        // HU-83 Sprint 1A: Policies de acceso a registros
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);
        Gate::policy(ElectronicInvoice::class, ElectronicInvoicePolicy::class);
        Gate::policy(MonthlyPayment::class, MonthlyPaymentPolicy::class);

        // Campana del topbar: comunicados publicados visibles sin leer.
        View::composer('layouts.app', function ($view) {
            $count = 0;
            if (auth()->check()) {
                $count = app(\App\Services\ComunicadoAlcanceService::class)->noLeidosPara(auth()->user());
            }
            $view->with('comunicadosNoLeidos', $count);
        });
    }
}
