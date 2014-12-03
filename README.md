# Yandex OAuth2 client provider

[![Build Status](https://travis-ci.org/rakeev/oauth2-yandex.svg?branch=master)](https://travis-ci.org/rakeev/oauth2-yandex)
[![Latest Stable Version](https://poser.pugx.org/aego/oauth2-yandex/v/stable.svg)](https://packagist.org/packages/aego/oauth2-yandex)
[![License](https://poser.pugx.org/aego/oauth2-yandex/license.svg)](https://packagist.org/packages/aego/oauth2-yandex)

This package provides [Yandex](https://passport.yandex.ru) integration for [OAuth2 Client](https://github.com/thephpleague/oauth2-client) by the League.

## Installation

```sh
composer require aego/oauth2-yandex
```

## Usage

```php
$provider = new Aego\OAuth2\Client\Provider\Yandex([
    'clientId'  =>  'b80bb7740288fda1f201890375a60c8f',
    'clientSecret'  =>  'f23ccd066f8236c6f97a2a62d3f9f9f5',
    'redirectUri' => 'https://example.org/oauth-endpoint',
]);
```
