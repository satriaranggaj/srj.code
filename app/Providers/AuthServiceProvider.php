<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /*
         * Portfolio administration.
         *
         * Before this gate existed, any authenticated account could reach the
         * dashboard and edit or delete every project, technology and certificate.
         * Registration is closed by default, but the gate is the actual control:
         * it protects the dashboard even if registration is ever reopened.
         */
        Gate::define('access-admin', function (?User $user): bool {
            return $user !== null && $user->isAdmin();
        });
    }
}
