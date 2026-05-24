<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $notifications = \App\Models\Notification::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
                $unreadCount = $notifications->where('is_read', false)->count();
                $view->with(compact('notifications', 'unreadCount'));
            }
        });
        URL::forceScheme('https');
    }
}
