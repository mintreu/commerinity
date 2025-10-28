# Laravel Package for Recruitment, Jobs handle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mintreu/laravel-recruitment.svg?style=flat-square)](https://packagist.org/packages/mintreu/laravel-recruitment)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mintreu/laravel-recruitment/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mintreu/laravel-recruitment/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mintreu/laravel-recruitment/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mintreu/laravel-recruitment/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mintreu/laravel-recruitment.svg?style=flat-square)](https://packagist.org/packages/mintreu/laravel-recruitment)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require mintreu/laravel-recruitment
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-recruitment-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-recruitment-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-recruitment-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$laravelRecruitment = new Mintreu\LaravelRecruitment();
echo $laravelRecruitment->echoPhrase('Hello, Mintreu!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Krishanu](https://github.com/mintreu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
