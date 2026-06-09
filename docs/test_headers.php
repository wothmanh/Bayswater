<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

use Illuminate\Http\Request;

$request = Request::create('/', 'GET');
$response = $app->handle($request);

$headers = $response->headers->all();

echo "Status: " . $response->getStatusCode() . "\n\n";
foreach (['content-security-policy','x-frame-options','x-content-type-options','referrer-policy','permissions-policy','strict-transport-security'] as $h) {
    if (isset($headers[$h])) {
        echo strtoupper($h) . ": " . implode(', ', $headers[$h]) . "\n";
    } else {
        echo strtoupper($h) . ": (not set)\n";
    }
}