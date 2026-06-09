<?php

// Run the test and capture output
ob_start();
include 'test_quotation_extraction_scenarios.php';
$output = ob_get_clean();

// Write output to file for inspection
file_put_contents('test_results.txt', $output);

// Check if all tests passed
if (strpos($output, 'Failed: 0') !== false) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "Total tests completed successfully.\n";
    exit(0);
} else {
    echo "❌ Some tests failed. Check test_results.txt for details.\n";
    // Extract failed count
    if (preg_match('/Failed: (\d+)/', $output, $matches)) {
        echo "Failed tests: {$matches[1]}\n";
    }
    exit(1);
}