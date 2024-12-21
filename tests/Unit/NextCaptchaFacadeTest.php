<?php

namespace NextCaptcha\Tests\Unit;

use NextCaptcha\Facades\NextCaptcha;
use NextCaptcha\NextCaptchaAPI;
use NextCaptcha\Tests\TestCase;

class NextCaptchaFacadeTest extends TestCase
{
    /** @test */
    public function it_resolves_facade_correctly(): void
    {
        $this->assertInstanceOf(
            NextCaptchaAPI::class,
            NextCaptcha::getFacadeRoot()
        );
    }

    /** @test */
    public function it_can_call_api_methods_through_facade(): void
    {
        // Mock the API response
        $this->mock(NextCaptchaAPI::class, function ($mock) {
            $mock->shouldReceive('getBalance')
                ->once()
                ->andReturn('100.00');
        });

        $balance = NextCaptcha::getBalance();

        $this->assertEquals('100.00', $balance);
    }
}
