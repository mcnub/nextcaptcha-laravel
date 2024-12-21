<?php

namespace NextCaptcha\Tests\Feature;

use NextCaptcha\NextCaptchaAPI;
use NextCaptcha\Tests\TestCase;

class NextCaptchaIntegrationTest extends TestCase
{
    /** @test */
    public function it_registers_singleton(): void
    {
        $instance = app(NextCaptchaAPI::class);

        $this->assertInstanceOf(NextCaptchaAPI::class, $instance);
    }

    /** @test */
    public function it_loads_config(): void
    {
        $this->assertEquals('test-key', config('nextcaptcha.client_key'));
        $this->assertEquals('test-soft-id', config('nextcaptcha.soft_id'));
        $this->assertEquals('https://example.com/callback', config('nextcaptcha.callback_url'));
    }

    /** @test */
    public function it_can_be_configured_through_env(): void
    {
        config(['nextcaptcha.client_key' => 'new-test-key']);

        $instance = app(NextCaptchaAPI::class);

        $reflection = new \ReflectionClass($instance);
        $property = $reflection->getProperty('clientKey');
        $property->setAccessible(true);

        $this->assertEquals('new-test-key', $property->getValue($instance));
    }
}
