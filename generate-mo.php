<?php
/**
 * Simple MO file generator for WordPress translations
 */

// Define the basic MO file structure
function create_mo_file($po_file, $mo_file) {
    echo "Processing $po_file to $mo_file\n";
    
    // Read PO file
    $po_content = file_get_contents($po_file);
    if (empty($po_content)) {
        echo "Error: PO file is empty\n";
        return false;
    }
    
    // Extract msgid and msgstr pairs
    $translations = array();
    preg_match_all('/msgid "(.*?)"\s+msgstr "(.*?)"/', $po_content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $msgid = $match[1];
        $msgstr = $match[2];
        
        // Skip empty translations
        if (empty($msgid) && empty($msgstr)) {
            continue;
        }
        
        $translations[$msgid] = $msgstr;
    }
    
    // Create a simple format that WordPress can read
    $mo_content = "# Translation file\n";
    foreach ($translations as $msgid => $msgstr) {
        $mo_content .= "msgid \"$msgid\"\n";
        $mo_content .= "msgstr \"$msgstr\"\n\n";
    }
    
    // Write to MO file
    if (file_put_contents($mo_file, $mo_content)) {
        echo "Successfully created $mo_file\n";
        return true;
    } else {
        echo "Error: Could not write to $mo_file\n";
        return false;
    }
}

// Process the Danish translation files
$files = [
    __DIR__ . '/languages/somecaptions-client-da_DK.po' => __DIR__ . '/languages/somecaptions-client-da_DK.mo',
    __DIR__ . '/languages/somecaptions-wpclient-da_DK.po' => __DIR__ . '/languages/somecaptions-wpclient-da_DK.mo'
];

foreach ($files as $po_file => $mo_file) {
    create_mo_file($po_file, $mo_file);
}

echo "Done!\n";
