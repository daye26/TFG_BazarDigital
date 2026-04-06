<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('components.layouts.store', function ($view): void {
            $cartItemsCount = 0;
            $user = Auth::user();

            if ($user && ! $user->isAdmin()) {
                $cartItemsCount = (int) $user->cartItems()->sum('quantity');
            }

            $view->with('storeCartItemsCount', $cartItemsCount);
        });
    }
}
