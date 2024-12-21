<?php

namespace NextCaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use NextCaptcha\NextCaptchaAPI;

/**
 * @method static array recaptchaV2(string $websiteUrl, string $websiteKey, string $recaptchaDataSValue = '', bool $isInvisible = false, string $apiDomain = '', string $pageAction = '', string $websiteInfo = '')
 * @method static array recaptchaV2Enterprise(string $websiteUrl, string $websiteKey, array $enterprisePayload = [], bool $isInvisible = false, string $apiDomain = '', string $pageAction = '', string $websiteInfo = '')
 * @method static array recaptchaV2HsEnterprise(string $websiteUrl, string $websiteKey, array $enterprisePayload = [], bool $isInvisible = false, string $apiDomain = '', string $pageAction = '', string $websiteInfo = '')
 * @method static array recaptchaV3(string $websiteUrl, string $websiteKey, string $pageAction = '', string $apiDomain = '', string $proxyType = '', string $proxyAddress = '', int $proxyPort = 0, string $proxyLogin = '', string $proxyPassword = '', string $websiteInfo = '')
 * @method static array recaptchaV3Hs(string $websiteUrl, string $websiteKey, string $pageAction = '', string $apiDomain = '', string $websiteInfo = '')
 * @method static array recaptchaMobile(string $appKey, string $appPackageName = '', string $appAction = '', string $proxyType = '', string $proxyAddress = '', int $proxyPort = 0, string $proxyLogin = '', string $proxyPassword = '', string $appDevice = 'ios')
 * @method static array hCaptcha(string $websiteUrl, string $websiteKey, bool $isInvisible = false, array $enterprisePayload = [], string $proxyType = '', string $proxyAddress = '', int $proxyPort = 0, string $proxyLogin = '', string $proxyPassword = '')
 * @method static array hCaptchaEnterprise(string $websiteUrl, string $websiteKey, array $enterprisePayload = [], bool $isInvisible = false, string $proxyType = '', string $proxyAddress = '', int $proxyPort = 0, string $proxyLogin = '', string $proxyPassword = '')
 * @method static string getBalance()
 *
 * @see NextCaptchaAPI
 */
class NextCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return NextCaptchaAPI::class;
    }
}
