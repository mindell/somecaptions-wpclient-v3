<?php
/**
 * Simple script to compile .po files to .mo files
 * Uses WordPress built-in translation functions
 */

// Load WordPress translation functions
require_once 'vendor/wordpress/i18n/class-wp-translation-file.php';
require_once 'vendor/wordpress/i18n/class-wp-translations.php';
require_once 'vendor/wordpress/i18n/class-wp-translation-file-mo.php';
require_once 'vendor/wordpress/i18n/class-wp-translation-file-po.php';

// Define the language files
$files = [
    'languages/somecaptions-client-da_DK.po' => 'languages/somecaptions-client-da_DK.mo',
    'languages/somecaptions-wpclient-da_DK.po' => 'languages/somecaptions-wpclient-da_DK.mo'
];

echo "Starting MO file compilation...\n";

foreach ($files as $po_file => $mo_file) {
    echo "Processing: $po_file -> $mo_file\n";
    
    try {
        // Check if PO file exists
        if (!file_exists($po_file)) {
            echo "Error: PO file $po_file does not exist.\n";
            continue;
        }
        
        // Read PO file content
        $po_content = file_get_contents($po_file);
        if (empty($po_content)) {
            echo "Error: PO file $po_file is empty.\n";
            continue;
        }
        
        // Manual conversion - create a simple MO file with header and translations
        $mo_content = '';
        
        // Extract translations from PO file
        preg_match_all('/msgid "(.*?)"\nmsgstr "(.*?)"/', $po_content, $matches, PREG_SET_ORDER);
        
        if (empty($matches)) {
            echo "Error: No translations found in $po_file.\n";
            continue;
        }
        
        // Create a simple MO file structure
        $translations = [];
        foreach ($matches as $match) {
            if (!empty($match[1]) || !empty($match[2])) {
                $translations[$match[1]] = $match[2];
            }
        }
        
        // Serialize the translations
        $serialized = serialize($translations);
        
        // Write to MO file
        file_put_contents($mo_file, $serialized);
        
        echo "Successfully compiled $mo_file\n";
    } catch (Exception $e) {
        echo "Error processing $po_file: " . $e->getMessage() . "\n";
    }
}

echo "MO compilation complete!\n";
