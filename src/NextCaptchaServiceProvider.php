<?php

namespace NextCaptcha;

use Illuminate\Support\ServiceProvider;

class NextCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nextcaptcha.php',
            'nextcaptcha'
        );

        $this->app->singleton(NextCaptchaAPI::class, function ($app) {
            return new NextCaptchaAPI(
                config('nextcaptcha.client_key'),
                config('nextcaptcha.soft_id'),
                config('nextcaptcha.callback_url'),
                config('nextcaptcha.open_log')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nextcaptcha.php' => config_path('nextcaptcha.php'),
            ], 'nextcaptcha-config');
        }
    }
}
