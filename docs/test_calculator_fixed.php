<?php

// Test the calculator page after fixing the QuotationController
echo "Testing Calculator Page After Fix...\n";
echo "=====================================\n";

// Test the calculator page URL
$url = 'http://localhost:8000/admin/quotations/create';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Calculator page loaded successfully (HTTP $httpCode)\n";
    
    // Check for accommodation options with private bathroom and dietary supplement data
    if (preg_match_all('/<option[^>]*value="(\d+)"[^>]*data-private-bathroom-enabled="(\d)"[^>]*data-dietary-supplement-enabled="(\d)"[^>]*>([^<]+)<\/option>/', $response, $matches, PREG_SET_ORDER)) {
        echo "\n✓ Found accommodation options with private bathroom and dietary supplement data:\n";
        foreach ($matches as $match) {
            $id = $match[1];
            $privateBathroom = $match[2];
            $dietarySupplement = $match[3];
            $name = trim($match[4]);
            echo "  - ID: $id, Name: $name, Private Bathroom: $privateBathroom, Dietary Supplement: $dietarySupplement\n";
        }
    } else {
        echo "✗ No accommodation options with private bathroom and dietary supplement data found\n";
    }
    
    // Check for private bathroom div
    if (strpos($response, 'id="private_bathroom_div"') !== false) {
        echo "\n✓ private_bathroom_div found in HTML\n";
    } else {
        echo "\n✗ private_bathroom_div NOT found in HTML\n";
    }
    
    // Check for dietary supplement div
    if (strpos($response, 'id="dietary_supplement_div"') !== false) {
        echo "✓ dietary_supplement_div found in HTML\n";
    } else {
        echo "✗ dietary_supplement_div NOT found in HTML\n";
    }
    
    // Check for toggleAccommodationOptions function
    if (strpos($response, 'function toggleAccommodationOptions()') !== false) {
        echo "✓ toggleAccommodationOptions function found\n";
    } else {
        echo "✗ toggleAccommodationOptions function NOT found\n";
    }
    
    // Check for accommodation select event listener
    if (strpos($response, 'accommodationSelect.addEventListener') !== false) {
        echo "✓ accommodationSelect event listener found\n";
    } else {
        echo "✗ accommodationSelect event listener NOT found\n";
    }
    
} else {
    echo "✗ Failed to load calculator page (HTTP $httpCode)\n";
}

echo "\nTest completed.\n";
?>