<?php
/**
 * Proper MO file generator for WordPress translations
 * Creates binary MO files that WordPress can read
 */

// Simple MO file writer class
class MOWriter {
    private $header = '';
    private $translations = array();
    
    public function addHeader($header) {
        $this->header = $header;
    }
    
    public function addTranslation($original, $translation) {
        if (!empty($original) || !empty($translation)) {
            $this->translations[$original] = $translation;
        }
    }
    
    public function parsePoFile($poFile) {
        $content = file_get_contents($poFile);
        if (empty($content)) {
            return false;
        }
        
        // Extract header
        preg_match('/msgid ""\s+msgstr "(.*?)"/s', $content, $headerMatch);
        if (!empty($headerMatch[1])) {
            $this->addHeader(str_replace('\n', "\n", $headerMatch[1]));
        }
        
        // Extract translations
        preg_match_all('/msgid "(.*?)"\s+msgstr "(.*?)"/s', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (isset($match[1]) && isset($match[2]) && ($match[1] !== '' || $match[2] !== '')) {
                $this->addTranslation($match[1], $match[2]);
            }
        }
        
        return true;
    }
    
    public function writeMoFile($moFile) {
        // MO file format constants
        $magic = 0x950412de;
        $revision = 0;
        $numStrings = count($this->translations) + 1; // +1 for header
        $origTableOffset = 28; // Size of MO header
        $transTableOffset = $origTableOffset + 8 * $numStrings;
        
        // Prepare data
        $originals = array('');
        $translations = array($this->header);
        
        foreach ($this->translations as $original => $translation) {
            $originals[] = $original;
            $translations[] = $translation;
        }
        
        // Calculate string offsets
        $stringOffset = $transTableOffset + 8 * $numStrings;
        $origOffsets = array();
        $transOffsets = array();
        
        $offset = $stringOffset;
        foreach ($originals as $original) {
            $length = strlen($original);
            $origOffsets[] = array($length, $offset);
            $offset += $length + 1; // +1 for null terminator
        }
        
        foreach ($translations as $translation) {
            $length = strlen($translation);
            $transOffsets[] = array($length, $offset);
            $offset += $length + 1; // +1 for null terminator
        }
        
        // Write MO file
        $fp = fopen($moFile, 'wb');
        if (!$fp) {
            return false;
        }
        
        // Write header
        fwrite($fp, pack('V', $magic));
        fwrite($fp, pack('V', $revision));
        fwrite($fp, pack('V', $numStrings));
        fwrite($fp, pack('V', $origTableOffset));
        fwrite($fp, pack('V', $transTableOffset));
        fwrite($fp, pack('V', 0)); // Size of hashing table
        fwrite($fp, pack('V', 0)); // Offset of hashing table
        
        // Write original string table
        foreach ($origOffsets as $offset) {
            fwrite($fp, pack('VV', $offset[0], $offset[1]));
        }
        
        // Write translation string table
        foreach ($transOffsets as $offset) {
            fwrite($fp, pack('VV', $offset[0], $offset[1]));
        }
        
        // Write strings
        foreach ($originals as $original) {
            fwrite($fp, $original . "\0");
        }
        
        foreach ($translations as $translation) {
            fwrite($fp, $translation . "\0");
        }
        
        fclose($fp);
        return true;
    }
}

// Process the Danish translation files
$files = [
    __DIR__ . '/languages/somecaptions-client-da_DK.po' => __DIR__ . '/languages/somecaptions-client-da_DK.mo',
    __DIR__ . '/languages/somecaptions-wpclient-da_DK.po' => __DIR__ . '/languages/somecaptions-wpclient-da_DK.mo'
];

echo "Starting binary MO file compilation...\n";

foreach ($files as $poFile => $moFile) {
    echo "Processing: $poFile -> $moFile\n";
    
    $writer = new MOWriter();
    if ($writer->parsePoFile($poFile)) {
        if ($writer->writeMoFile($moFile)) {
            echo "Successfully compiled $moFile\n";
        } else {
            echo "Error writing to $moFile\n";
        }
    } else {
        echo "Error parsing $poFile\n";
    }
}

echo "MO compilation complete!\n";
