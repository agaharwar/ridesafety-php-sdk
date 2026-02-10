<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use RideSafety\RideSafetyClient;

$client = new RideSafetyClient(
    apiKey: 'YOUR_API_KEY',
    baseUrl: 'https://ridesafety.app'
);

$recalls = $client->getRecalls(['vin' => '1HGBH41JXMN109186']);
print "recalls: " . ($recalls['total_recalls'] ?? 'n/a') . PHP_EOL;

$safety = $client->getSafetyRatings('Honda', 'Civic', 2020);
$overall = $safety['data']['nhtsa_rating']['overall'] ?? 'n/a';
print "safety rating: {$overall}" . PHP_EOL;

try {
    $diagnostics = $client->runDiagnostics([
        'vin' => '1HGBH41JXMN109186',
        'symptoms' => 'Rough idle and engine misfire',
        'dtc_codes' => ['P0300'],
    ]);
    $issue = $diagnostics['data']['diagnosis']['primary_issue'] ?? 'n/a';
    print "diagnostics: {$issue}" . PHP_EOL;
} catch (Throwable $e) {
    print "diagnostics failed (paid plan required): {$e->getMessage()}" . PHP_EOL;
}
