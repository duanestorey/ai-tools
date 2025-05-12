<?php

require __DIR__.'/vendor/autoload.php';

use DuaneStorey\AiTools\Core\Configuration;

// Get the project root
$projectRoot = __DIR__;

// Load the configuration
$config = new Configuration($projectRoot);

// Get the excluded directories
$excludedDirs = $config->get('excluded_directories', []);

echo "Excluded directories from config:\n";
foreach ($excludedDirs as $dir) {
    echo "- {$dir}\n";
}

// Test a specific path
$testPath = 'src/Viewers/Laravel';
echo "\nTesting if '{$testPath}' is excluded:\n";

// Check if the path is in the excluded directories list
if (in_array($testPath, $excludedDirs)) {
    echo "YES - '{$testPath}' is in the excluded directories list\n";
} else {
    echo "NO - '{$testPath}' is NOT in the excluded directories list\n";
}

// Test a simple directory name
$testDir = 'Laravel';
echo "\nTesting if '{$testDir}' is excluded:\n";

// Check if the directory name is in the excluded directories list
if (in_array($testDir, $excludedDirs)) {
    echo "YES - '{$testDir}' is in the excluded directories list\n";
} else {
    echo "NO - '{$testDir}' is NOT in the excluded directories list\n";
}
