<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPublicLayoutComponent();
        $this->configureRateLimiting();
    }

    /**
     * `resources/views/layouts/portfolio.blade.php` is the public shell. Registering
     * it as a Blade alias lets the public views use the slot-based component syntax
     * (<x-layouts.portfolio> … </x-layouts.portfolio>) while keeping the layout in
     * the conventional `layouts/` directory.
     *
     * The admin layouts (layouts/app, layouts/guest) are untouched.
     */
    private function registerPublicLayoutComponent(): void
    {
        Blade::component('layouts.portfolio', 'layouts.portfolio');
    }

    /**
     * The public contact form is the only unauthenticated write endpoint on the
     * site, so it gets an explicit, keyed rate limit.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute((int) config('portfolio.contact_form.max_per_minute', 5))
                ->by($request->ip());
        });
    }
}
