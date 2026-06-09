<?php
// Get the manifest file contents
$manifestPath = __DIR__ . '/public/build/manifest.json';
if (!file_exists($manifestPath)) {
    echo "Manifest file not found at: $manifestPath\n";
    exit(1);
}

$manifest = json_decode(file_get_contents($manifestPath), true);
if (!$manifest) {
    echo "Failed to parse manifest file\n";
    exit(1);
}

// Get the current CSS and JS asset paths
$cssAssets = [];
$jsAssets = [];

foreach ($manifest as $key => $entry) {
    if (strpos($key, 'resources/css') === 0) {
        $cssAssets[] = $entry['file'];
    } elseif (strpos($key, 'resources/js') === 0) {
        $jsAssets[] = $entry['file'];
    }
}

// Output the asset paths
echo "CSS Assets:\n";
foreach ($cssAssets as $asset) {
    echo "- $asset\n";
}

echo "\nJS Assets:\n";
foreach ($jsAssets as $asset) {
    echo "- $asset\n";
}

// Output the code to use in the layout file
echo "\n\nUpdate your layout file with:\n\n";
echo "<!-- Styles -->\n";
foreach ($cssAssets as $asset) {
    echo "<link rel=\"stylesheet\" href=\"{{ asset('build/$asset') }}\">\n";
}
echo "<link rel=\"stylesheet\" href=\"{{ asset('css/bayswater-styles.css') }}\">\n\n";

echo "<!-- Scripts -->\n";
foreach ($jsAssets as $asset) {
    echo "<script src=\"{{ asset('build/$asset') }}\" defer></script>\n";
}
