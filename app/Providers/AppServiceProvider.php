<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
            SymfonyRequest::setTrustedProxies(
                ['0.0.0.0/0', '::/0'],
                SymfonyRequest::HEADER_X_FORWARDED_FOR
                | SymfonyRequest::HEADER_X_FORWARDED_HOST
                | SymfonyRequest::HEADER_X_FORWARDED_PROTO
                | SymfonyRequest::HEADER_X_FORWARDED_PORT
            );

            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }
    }
}
