<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        View::composer('layouts.app', function ($view) {
            $cart = app(CartService::class)->existingCart(request());
            $cart?->load('items.product.defaultImage');

            $view->with('headerCartItems', $cart?->items ?? collect());
        });
    }
}
