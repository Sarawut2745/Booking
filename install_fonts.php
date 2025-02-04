<?php
// Set the FPDF font directory
$fontDir = __DIR__ . '/fpdf/font/';

// Create font directory if it doesn't exist
if (!is_dir($fontDir)) {
    mkdir($fontDir, 0755, true);
}

// Core FPDF fonts
$fonts = array(
    'helvetica.php',
    'helveticab.php',
    'helveticai.php',
    'helveticabi.php',
    'times.php',
    'timesb.php',
    'timesi.php',
    'timesbi.php',
    'courier.php',
    'courierb.php',
    'courieri.php',
    'courierbi.php',
    'symbol.php',
    'zapfdingbats.php'
);

// Base URL for FPDF font files
$baseUrl = 'http://www.fpdf.org/font/';

// Download each font file
foreach ($fonts as $font) {
    $fontPath = $fontDir . $font;
    if (!file_exists($fontPath)) {
        $content = file_get_contents($baseUrl . $font);
        if ($content !== false) {
            file_put_contents($fontPath, $content);
            echo "Downloaded: $font<br>";
        } else {
            echo "Failed to download: $font<br>";
        }
    } else {
        echo "File already exists: $font<br>";
    }
}

echo "Font installation complete!";
?>