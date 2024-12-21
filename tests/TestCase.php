<?php

namespace NextCaptcha\Tests;

use NextCaptcha\NextCaptchaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NextCaptchaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('nextcaptcha.client_key', 'test-key');
        $app['config']->set('nextcaptcha.soft_id', 'test-soft-id');
        $app['config']->set('nextcaptcha.callback_url', 'https://example.com/callback');
        $app['config']->set('nextcaptcha.open_log', false);
    }
}
