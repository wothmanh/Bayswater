<?php

// Simple test to check if the calculator page loads and accommodation options work
echo "Testing calculator page functionality...\n";

// Use cURL to get the calculator page
$url = 'http://localhost:8000/admin/quotations/create';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $httpCode\n";

if ($httpCode === 200 && $response) {
    echo "Calculator page loaded successfully\n";
    
    // Check if accommodation options are present in the HTML
    if (strpos($response, 'data-private-bathroom-enabled="1"') !== false) {
        echo "✓ Found accommodation with private bathroom enabled\n";
    } else {
        echo "✗ No accommodation with private bathroom enabled found\n";
    }
    
    if (strpos($response, 'data-dietary-supplement-enabled="1"') !== false) {
        echo "✓ Found accommodation with dietary supplement enabled\n";
    } else {
        echo "✗ No accommodation with dietary supplement enabled found\n";
    }
    
    // Check if the private bathroom and dietary supplement divs exist
    if (strpos($response, 'id="private_bathroom_div"') !== false) {
        echo "✓ Private bathroom div found in HTML\n";
    } else {
        echo "✗ Private bathroom div not found in HTML\n";
    }
    
    if (strpos($response, 'id="dietary_supplement_div"') !== false) {
        echo "✓ Dietary supplement div found in HTML\n";
    } else {
        echo "✗ Dietary supplement div not found in HTML\n";
    }
    
    // Check if toggleAccommodationOptions function exists
    if (strpos($response, 'function toggleAccommodationOptions()') !== false) {
        echo "✓ toggleAccommodationOptions function found\n";
    } else {
        echo "✗ toggleAccommodationOptions function not found\n";
    }
    
    // Check if accommodation select event listener exists
    if (strpos($response, 'accommodationSelect.addEventListener') !== false) {
        echo "✓ Accommodation select event listener found\n";
    } else {
        echo "✗ Accommodation select event listener not found\n";
    }
    
} else {
    echo "Failed to load calculator page. HTTP Code: $httpCode\n";
    if ($response) {
        echo "Response: " . substr($response, 0, 500) . "...\n";
    }
}

echo "\nTest completed.\n";