<?php

namespace App\Providers;

use App\Models\GradeScore;
use App\Observers\GradeScoreObserver;
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
        GradeScore::observe(GradeScoreObserver::class);

        Password::defaults(function () {
            return Password::min(8);
        });
    }
}
