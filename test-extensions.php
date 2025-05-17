<?php

// Test script for file extension filtering
$extensions = ['.php', '.json', '.md'];
$testExtensions = ['.php', '.json', '.md', '.rb', '.yml', '.yaml'];

echo "Testing extension matching with in_array:\n";

foreach ($testExtensions as $ext) {
    $result = in_array($ext, $extensions, true) ? "MATCH" : "NO MATCH";
    echo "$ext - $result\n";
}

// Test with some actual file names
$files = [
    'test.php',
    'test.json',
    'test.md',
    'test.rb',
    'test.yml',
    'README.md',
    'composer.json',
    'ai-overview.rb'
];

echo "\nTesting with file names:\n";

foreach ($files as $file) {
    $extension = strtolower('.' . pathinfo($file, PATHINFO_EXTENSION));
    $result = in_array($extension, $extensions, true) ? "MATCH" : "NO MATCH";
    echo "$file (extension: $extension) - $result\n";
} 