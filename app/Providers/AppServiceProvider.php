<?php

namespace App\Providers;

use App\Events\OrderPlacedBroadcast;
use App\Listeners\SendOrderConfirmedNotification;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Gate::policy(Role::class, RolePolicy::class);
    }
}
