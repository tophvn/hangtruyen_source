<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('vi');
        
        view()->composer('*', function ($view) {
            $view->with('categories', \App\Models\Category::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get());
        });
    }
}
