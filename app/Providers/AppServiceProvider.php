<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

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
        $forceHttps = $this->app->environment('production')
            || str_starts_with(env('APP_URL', ''), 'https://')
            || str_starts_with(env('ASSET_URL', ''), 'https://');

        if ($forceHttps) {
            Request::setTrustedProxies(
                ['0.0.0.0/0', '::/0'],
                Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
            );

            URL::forceScheme('https');
        }
    }
}
