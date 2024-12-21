<?php

namespace NextCaptcha\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use NextCaptcha\NextCaptchaAPI;
use NextCaptcha\Tests\TestCase;

class NextCaptchaAPITest extends TestCase
{
    private MockHandler $mockHandler;

    private NextCaptchaAPI $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler;
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->api = new NextCaptchaAPI(
            clientKey: 'test-key',
            softId: 'test-soft-id',
            callbackUrl: 'https://example.com/callback'
        );

        // Replace the Guzzle client with our mocked version
        $reflection = new \ReflectionClass($this->api);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->api, $client);
    }

    /** @test */
    public function it_can_get_balance(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'balance' => '100.00',
            ]))
        );

        $balance = $this->api->getBalance();

        $this->assertEquals('100.00', $balance);
    }

    /** @test */
    public function it_can_solve_recaptcha_v2(): void
    {
        // Mock the create task response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'taskId' => '123456',
                'status' => 'processing',
            ]))
        );

        // Mock the get task result response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'status' => 'ready',
                'solution' => [
                    'gRecaptchaResponse' => 'test-response',
                ],
            ]))
        );

        $result = $this->api->recaptchaV2(
            websiteUrl: 'https://example.com',
            websiteKey: 'test-site-key'
        );

        $this->assertEquals('ready', $result['status']);
        $this->assertEquals('test-response', $result['solution']['gRecaptchaResponse']);
    }

    /** @test */
    public function it_handles_failed_tasks(): void
    {
        // Mock the create task response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'taskId' => '123456',
                'status' => 'processing',
            ]))
        );

        // Mock the get task result response with failure
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'status' => 'failed',
                'errorCode' => '1',
                'errorDescription' => 'Test error',
            ]))
        );

        $result = $this->api->recaptchaV2(
            websiteUrl: 'https://example.com',
            websiteKey: 'test-site-key'
        );

        $this->assertEquals('failed', $result['status']);
        $this->assertEquals('Test error', $result['errorDescription']);
    }

    public function it_handles_timeout(): void
    {
        // Mock the create task response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'taskId' => '123456',
                'status' => 'processing',
            ]))
        );

        // Set initial time
        $this->api->setCurrentTime(1000);

        $result = $this->api->recaptchaV2(
            websiteUrl: 'https://example.com',
            websiteKey: 'test-site-key'
        );

        $this->assertEquals('failed', $result['status']);
        $this->assertEquals('Timeout', $result['errorDescription']);
    }

    /** @test */
    public function it_can_solve_recaptcha_v3(): void
    {
        // Mock the create task response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'taskId' => '123456',
                'status' => 'processing',
            ]))
        );

        // Mock the get task result response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'status' => 'ready',
                'solution' => [
                    'gRecaptchaResponse' => 'test-response',
                    'score' => 0.9,
                ],
            ]))
        );

        $result = $this->api->recaptchaV3(
            websiteUrl: 'https://example.com',
            websiteKey: 'test-site-key',
            pageAction: 'login'
        );

        $this->assertEquals('ready', $result['status']);
        $this->assertEquals('test-response', $result['solution']['gRecaptchaResponse']);
        $this->assertEquals(0.9, $result['solution']['score']);
    }

    /** @test */
    public function it_can_solve_hcaptcha(): void
    {
        // Mock the create task response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'taskId' => '123456',
                'status' => 'processing',
            ]))
        );

        // Mock the get task result response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'status' => 'ready',
                'solution' => [
                    'gRecaptchaResponse' => 'test-response',
                ],
            ]))
        );

        $result = $this->api->hCaptcha(
            websiteUrl: 'https://example.com',
            websiteKey: 'test-site-key'
        );

        $this->assertEquals('ready', $result['status']);
        $this->assertEquals('test-response', $result['solution']['gRecaptchaResponse']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
