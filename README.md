# RideSafety PHP SDK

## Install

```bash
composer require ridesafety/sdk
```

## Publish (Packagist)

1. Push this repo to GitHub.
2. Create a release tag (e.g. `v0.1.0`).
3. Add the repo to Packagist.

## Usage

```php
<?php

use RideSafety\RideSafetyClient;

$client = new RideSafetyClient(
    apiKey: 'YOUR_API_KEY',
    baseUrl: 'https://ridesafety.app'
);

$recalls = $client->getRecalls(['vin' => '1HGBH41JXMN109186']);

$diagnostics = $client->runDiagnostics([
    'vin' => '1HGBH41JXMN109186',
    'symptoms' => 'Rough idle and engine misfire',
    'dtc_codes' => ['P0300'],
]);

$safety = $client->getSafetyRatings('Honda', 'Civic', 2020);
```

## Smoke test (all APIs)

```bash
cd sdk/php
composer install
php smoke_test.php
```

Edit `smoke_test.php` to set your API key. Diagnostics may return 402 on free plan.

## Auth modes

By default the SDK uses `Authorization: Bearer <key>`. To use `x-api-key` instead:

```php
$client = new RideSafetyClient(apiKey: 'KEY', authMode: 'x-api-key');
```
