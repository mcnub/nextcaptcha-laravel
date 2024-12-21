# NextCaptcha for Laravel

## Features

  - reCAPTCHA v2
  - reCAPTCHA v2 Enterprise
  - reCAPTCHA v2 HS Enterprise
  - reCAPTCHA v3
  - reCAPTCHA v3 HS
  
- Laravel Facade for easy usage
- Configurable through Laravel's configuration system
- Comprehensive logging support
- Proxy support
- Full TypeScript support for response types

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher
- Guzzle 7.5 or higher

## Installation

You can install the package via composer:

```bash
composer require mcnub/nextcaptcha-laravel
```

After installing, publish the configuration file:

```bash
php artisan vendor:publish --tag=nextcaptcha-config
```

## Configuration

Configure your NextCaptcha credentials in your `.env` file:

```env
NEXTCAPTCHA_CLIENT_KEY=your-api-key
NEXTCAPTCHA_TIMEOUT=45
```

## Usage

### Basic Usage

Using the Facade:

```php
use NextCaptcha\Facades\NextCaptcha;

// Solve reCAPTCHA v2
$result = NextCaptcha::recaptchaV2(
    websiteUrl: 'https://example.com',
    websiteKey: 'site-key'
);

// Get balance
$balance = NextCaptcha::getBalance();
```

Using Dependency Injection:

```php
use NextCaptcha\NextCaptchaAPI;

class CaptchaController extends Controller
{
    public function __construct(
        private NextCaptchaAPI $captcha
    ) {}

    public function solve()
    {
        $result = $this->captcha->recaptchaV2(
            websiteUrl: 'https://example.com',
            websiteKey: 'site-key'
        );
        
        return response()->json($result);
    }
}
```

### Available Methods

#### reCAPTCHA v2

```php
$result = NextCaptcha::recaptchaV2(
    websiteUrl: 'https://example.com',
    websiteKey: 'site-key',
    recaptchaDataSValue: '',  // optional
    isInvisible: false,       // optional
    apiDomain: '',           // optional
    pageAction: '',          // optional
    websiteInfo: ''          // optional
);
```

#### reCAPTCHA v2 Enterprise

```php
$result = NextCaptcha::recaptchaV2Enterprise(
    websiteUrl: 'https://example.com',
    websiteKey: 'site-key',
    enterprisePayload: [],    // optional
    isInvisible: false,       // optional
    apiDomain: '',           // optional
    pageAction: '',          // optional
    websiteInfo: ''          // optional
);
```

#### reCAPTCHA v3

```php
$result = NextCaptcha::recaptchaV3(
    websiteUrl: 'https://example.com',
    websiteKey: 'site-key',
    pageAction: '',          // optional
    apiDomain: '',           // optional
    proxyType: '',          // optional
    proxyAddress: '',       // optional
    proxyPort: 0,           // optional
    proxyLogin: '',         // optional
    proxyPassword: '',      // optional
    websiteInfo: ''         // optional
);
```

#### hCaptcha

```php
$result = NextCaptcha::hCaptcha(
    websiteUrl: 'https://example.com',
    websiteKey: 'site-key',
    isInvisible: false,      // optional
    enterprisePayload: [],   // optional
    proxyType: '',          // optional
    proxyAddress: '',       // optional
    proxyPort: 0,           // optional
    proxyLogin: '',         // optional
    proxyPassword: ''       // optional
);
```

## Testing

```bash
composer test
```

## Security Vulnerabilities

If you discover a security vulnerability within NextCaptcha Laravel, please send an e-mail to security@example.com. All security vulnerabilities will be promptly addressed.

## Credits

- [Nextcaptcha](https://github.com/nextcaptcha)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.