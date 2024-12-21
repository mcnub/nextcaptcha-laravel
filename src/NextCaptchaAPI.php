<?php

namespace NextCaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NextCaptchaAPI
{
    private const HOST = 'https://api.nextcaptcha.com';

    private const TIMEOUT = 45;

    // Captcha Types
    private const RECAPTCHAV2_TYPE = 'RecaptchaV2TaskProxyless';

    private const RECAPTCHAV2_ENTERPRISE_TYPE = 'RecaptchaV2EnterpriseTaskProxyless';

    private const RECAPTCHAV2HS_ENTERPRISE_TYPE = 'RecaptchaV2HSEnterpriseTaskProxyless';

    private const RECAPTCHAV3_PROXYLESS_TYPE = 'RecaptchaV3TaskProxyless';

    private const RECAPTCHAV3HS_PROXYLESS_TYPE = 'RecaptchaV3HSTaskProxyless';

    private const RECAPTCHAV3_TYPE = 'RecaptchaV3Task';

    private const RECAPTCHA_MOBILE_PROXYLESS_TYPE = 'ReCaptchaMobileTaskProxyLess';

    private const RECAPTCHA_MOBILE_TYPE = 'ReCaptchaMobileTask';

    private const HCAPTCHA_TYPE = 'HCaptchaTask';

    private const HCAPTCHA_PROXYLESS_TYPE = 'HCaptchaTaskProxyless';

    private const HCAPTCHA_ENTERPRISE_TYPE = 'HCaptchaEnterpriseTask';

    // Status Types
    private const STATUS_PENDING = 'pending';

    private const STATUS_PROCESSING = 'processing';

    private const STATUS_READY = 'ready';

    private const STATUS_FAILED = 'failed';

    private string $clientKey;

    private string $softId;

    private string $callbackUrl;

    private bool $openLog;

    private Client $client;

    private float $startTime;

    /**
     * Send task to API and wait for result
     */
    protected $currentTime;

    public function __construct(
        string $clientKey,
        string $softId = '',
        string $callbackUrl = '',
        bool $openLog = true
    ) {
        $this->clientKey = $clientKey;
        $this->softId = $softId;
        $this->callbackUrl = $callbackUrl;
        $this->openLog = $openLog;

        $this->client = new Client([
            'verify' => false,
            'http_errors' => false,
            'pool_maxsize' => 1000,
        ]);

        if ($this->openLog) {
            Log::info("NextCaptchaAPI created with clientKey={$clientKey} softId={$softId} callbackUrl={$callbackUrl}");
        }
    }

    /**
     * Get account balance
     */
    public function getBalance(): string
    {
        try {
            $response = $this->client->post(self::HOST . '/getBalance', [
                'json' => ['clientKey' => $this->clientKey],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                if ($this->openLog) {
                    Log::error("Error: {$response->getStatusCode()} " . json_encode($result));
                }

                return $result;
            }

            if ($this->openLog) {
                Log::info("Balance: {$result['balance']}");
            }

            return $result['balance'];
        } catch (GuzzleException $e) {
            if ($this->openLog) {
                Log::error("Error getting balance: {$e->getMessage()}");
            }

            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getCurrentTime(): int
    {
        return $this->currentTime ?? time();
    }

    public function setCurrentTime(int $time): void
    {
        $this->currentTime = $time;
    }

    private function sendTask(array $task): array
    {
        try {
            $data = [
                'clientKey' => $this->clientKey,
                'softId' => $this->softId,
                'callbackUrl' => $this->callbackUrl,
                'task' => $task,
            ];

            $response = $this->client->post(self::HOST . '/createTask', [
                'json' => $data,
            ]);

            if ($response->getStatusCode() !== 200) {
                if ($this->openLog) {
                    Log::error("Error: {$response->getStatusCode()} " . $response->getBody()->getContents());
                    Log::error('Data: ' . json_encode($data));
                }

                return json_decode($response->getBody()->getContents(), true);
            }

            $result = json_decode($response->getBody()->getContents(), true);
            $taskId = $result['taskId'];

            if ($this->openLog) {
                Log::info("Task {$taskId} created " . json_encode($result));
            }

            $startTime = $this->getCurrentTime();
            while (true) {
                if ($this->getCurrentTime() - $startTime > self::TIMEOUT) {
                    return [
                        'errorId' => 12,
                        'errorDescription' => 'Timeout',
                        'status' => 'failed',
                    ];
                }

                $response = $this->client->post(self::HOST . '/getTaskResult', [
                    'json' => [
                        'clientKey' => $this->clientKey,
                        'taskId' => $taskId,
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    if ($this->openLog) {
                        Log::error("Error: {$response->getStatusCode()} " . $response->getBody()->getContents());
                    }

                    return json_decode($response->getBody()->getContents(), true);
                }

                $result = json_decode($response->getBody()->getContents(), true);
                $status = $result['status'];

                if ($this->openLog) {
                    Log::info("Task status: {$status}");
                }

                if ($status === self::STATUS_READY) {
                    if ($this->openLog) {
                        Log::info("Task {$taskId} ready " . json_encode($result));
                    }

                    return $result;
                }

                if ($status === self::STATUS_FAILED) {
                    if ($this->openLog) {
                        Log::error("Task {$taskId} failed " . json_encode($result));
                    }

                    return $result;
                }

                // For testing purposes, we simulate time passing
                if (isset($this->currentTime)) {
                    $this->currentTime += 1;
                }

                usleep(500000); // 0.5 second delay
            }
        } catch (GuzzleException $e) {
            if ($this->openLog) {
                Log::error("Error sending task: {$e->getMessage()}");
            }

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Solve reCAPTCHA v2 challenge
     */
    public function recaptchaV2(
        string $websiteUrl,
        string $websiteKey,
        string $recaptchaDataSValue = '',
        bool $isInvisible = false,
        string $apiDomain = '',
        string $pageAction = '',
        string $websiteInfo = ''
    ): array {
        $task = [
            'type' => self::RECAPTCHAV2_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'recaptchaDataSValue' => $recaptchaDataSValue,
            'isInvisible' => $isInvisible,
            'apiDomain' => $apiDomain,
            'pageAction' => $pageAction,
            'websiteInfo' => $websiteInfo,
        ];

        return $this->sendTask($task);
    }

    /**
     * Solve reCAPTCHA v2 Enterprise challenge
     */
    public function recaptchaV2Enterprise(
        string $websiteUrl,
        string $websiteKey,
        array $enterprisePayload = [],
        bool $isInvisible = false,
        string $apiDomain = '',
        string $pageAction = '',
        string $websiteInfo = ''
    ): array {
        $task = [
            'type' => self::RECAPTCHAV2_ENTERPRISE_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'enterprisePayload' => $enterprisePayload,
            'isInvisible' => $isInvisible,
            'apiDomain' => $apiDomain,
            'pageAction' => $pageAction,
            'websiteInfo' => $websiteInfo,
        ];

        return $this->sendTask($task);
    }

    /**
     * Solve reCAPTCHA v3 challenge
     */
    public function recaptchaV3(
        string $websiteUrl,
        string $websiteKey,
        string $pageAction = '',
        string $apiDomain = '',
        string $proxyType = '',
        string $proxyAddress = '',
        int $proxyPort = 0,
        string $proxyLogin = '',
        string $proxyPassword = '',
        string $websiteInfo = ''
    ): array {
        $task = [
            'type' => self::RECAPTCHAV3_PROXYLESS_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'pageAction' => $pageAction,
            'apiDomain' => $apiDomain,
            'websiteInfo' => $websiteInfo,
        ];

        if ($proxyAddress) {
            $task['type'] = self::RECAPTCHAV3_TYPE;
            $task['proxyType'] = $proxyType;
            $task['proxyAddress'] = $proxyAddress;
            $task['proxyPort'] = $proxyPort;
            $task['proxyLogin'] = $proxyLogin;
            $task['proxyPassword'] = $proxyPassword;
        }

        return $this->sendTask($task);
    }

    /**
     * Solve hCaptcha challenge
     */
    public function hCaptcha(
        string $websiteUrl,
        string $websiteKey,
        bool $isInvisible = false,
        array $enterprisePayload = [],
        string $proxyType = '',
        string $proxyAddress = '',
        int $proxyPort = 0,
        string $proxyLogin = '',
        string $proxyPassword = ''
    ): array {
        $task = [
            'type' => self::HCAPTCHA_PROXYLESS_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'isInvisible' => $isInvisible,
            'enterprisePayload' => $enterprisePayload,
        ];

        if ($proxyAddress) {
            $task['type'] = self::HCAPTCHA_TYPE;
            $task['proxyType'] = $proxyType;
            $task['proxyAddress'] = $proxyAddress;
            $task['proxyPort'] = $proxyPort;
            $task['proxyLogin'] = $proxyLogin;
            $task['proxyPassword'] = $proxyPassword;
        }

        return $this->sendTask($task);
    }

    /**
     * Solve reCAPTCHA v2 HS Enterprise challenge
     */
    public function recaptchaV2HsEnterprise(
        string $websiteUrl,
        string $websiteKey,
        array $enterprisePayload = [],
        bool $isInvisible = false,
        string $apiDomain = '',
        string $pageAction = '',
        string $websiteInfo = ''
    ): array {
        $task = [
            'type' => self::RECAPTCHAV2HS_ENTERPRISE_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'enterprisePayload' => $enterprisePayload,
            'isInvisible' => $isInvisible,
            'apiDomain' => $apiDomain,
            'pageAction' => $pageAction,
            'websiteInfo' => $websiteInfo,
        ];

        return $this->sendTask($task);
    }

    /**
     * Solve reCAPTCHA v3 HS challenge
     */
    public function recaptchaV3Hs(
        string $websiteUrl,
        string $websiteKey,
        string $pageAction = '',
        string $apiDomain = '',
        string $websiteInfo = ''
    ): array {
        $task = [
            'type' => self::RECAPTCHAV3HS_PROXYLESS_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'pageAction' => $pageAction,
            'apiDomain' => $apiDomain,
            'websiteInfo' => $websiteInfo,
        ];

        return $this->sendTask($task);
    }

    /**
     * Solve Mobile reCAPTCHA challenge
     */
    public function recaptchaMobile(
        string $appKey,
        string $appPackageName = '',
        string $appAction = '',
        string $proxyType = '',
        string $proxyAddress = '',
        int $proxyPort = 0,
        string $proxyLogin = '',
        string $proxyPassword = '',
        string $appDevice = 'ios'
    ): array {
        $task = [
            'type' => self::RECAPTCHA_MOBILE_PROXYLESS_TYPE,
            'appKey' => $appKey,
            'appPackageName' => $appPackageName,
            'appAction' => $appAction,
            'appDevice' => $appDevice,
        ];

        if ($proxyAddress !== '') {
            $task['type'] = self::RECAPTCHA_MOBILE_TYPE;
            $task['proxyType'] = $proxyType;
            $task['proxyAddress'] = $proxyAddress;
            $task['proxyPort'] = $proxyPort;
            $task['proxyLogin'] = $proxyLogin;
            $task['proxyPassword'] = $proxyPassword;
        }

        return $this->sendTask($task);
    }

    /**
     * Solve hCaptcha Enterprise challenge
     */
    public function hCaptchaEnterprise(
        string $websiteUrl,
        string $websiteKey,
        array $enterprisePayload = [],
        bool $isInvisible = false,
        string $proxyType = '',
        string $proxyAddress = '',
        int $proxyPort = 0,
        string $proxyLogin = '',
        string $proxyPassword = ''
    ): array {
        $task = [
            'type' => self::HCAPTCHA_ENTERPRISE_TYPE,
            'websiteURL' => $websiteUrl,
            'websiteKey' => $websiteKey,
            'enterprisePayload' => $enterprisePayload,
            'isInvisible' => $isInvisible,
            'proxyType' => $proxyType,
            'proxyAddress' => $proxyAddress,
            'proxyPort' => $proxyPort,
            'proxyLogin' => $proxyLogin,
            'proxyPassword' => $proxyPassword,
        ];

        return $this->sendTask($task);
    }

    /**
     * Create a new instance of NextCaptchaAPI
     */
    public static function make(
        string $clientKey,
        string $softId = '',
        string $callbackUrl = '',
        bool $openLog = true
    ): self {
        return new static($clientKey, $softId, $callbackUrl, $openLog);
    }
}
