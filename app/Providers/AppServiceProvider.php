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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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
        Password::defaults(function () {
            return Password::min(8);
        });

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
