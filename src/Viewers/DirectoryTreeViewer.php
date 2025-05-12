<?php

namespace DuaneStorey\AiTools\Viewers;

use DuaneStorey\AiTools\Core\Configuration;

class DirectoryTreeViewer implements ViewerInterface
{
    private ?string $lastHash = null;

    private Configuration $config;
    
    private string $projectRoot;

    public function __construct()
    {
        // No initialization needed
    }

    public function getName(): string
    {
        return 'Directory Tree';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Directory tree is always applicable
        return true;
    }

    public function generate(string $projectRoot): string
    {
        // Load configuration
        $this->config = new Configuration($projectRoot);

        $output = "# Directory Tree\n\n```\n";
        $output .= basename($projectRoot)."\n";
        
        // Store the project root for path calculations
        $this->projectRoot = $projectRoot;
        
        $this->buildTree($projectRoot, $output, 0, ''); // Pass empty string as initial relative path
        $output .= "\n```\n";

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        // Generate a hash of the directory structure
        $hash = $this->generateDirectoryHash($projectRoot);

        // Check if hash has changed
        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        // Update the hash
        $this->lastHash = $hash;

        return true;
    }

    /**
     * Build a directory tree recursively
     *
     * @param string $directory The current directory
     * @param string &$output   Reference to the output string
     * @param int    $depth     Current depth level
     * @param string $relativePath The relative path from the project root
     */
    private function buildTree(string $directory, string &$output, int $depth, string $relativePath = ''): void
    {
        // Get excluded directories and files from config
        $excludedDirs = $this->config->get('excluded_directories', ['.git', 'vendor', 'node_modules']);
        $excludedFiles = $this->config->get('excluded_files', []);
        
        // Check max depth
        $maxDepth = $this->config->get('directory_tree.max_depth', 4);
        
        if ($depth >= $maxDepth) {
            return;
        }

        // Get directory contents
        $items = scandir($directory);

        // Filter out . and .. and excluded items
        $filteredItems = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.'/'.$item;
            $isDir = is_dir($path);
            
            // Calculate the current relative path
            // Get the real path of the current directory
            $realPath = realpath($path);
            // Calculate the relative path from the project root
            $currentRelativePath = str_replace($this->projectRoot.'/', '', $realPath);
            
            // No debug statements needed

            // Skip excluded directories
            if ($isDir) {
                $shouldExclude = false;
                
                // Debug all directory paths at depth 3
                if ($depth === 3) {
                    echo "Processing directory at depth 3: {$currentRelativePath}\n";
                }
                
                // Check simple directory name exclusions
                if (in_array($item, $excludedDirs)) {
                    $shouldExclude = true;
                }
                
                // Check path-based exclusions
                foreach ($excludedDirs as $excludedDir) {
                    // Only process path-based exclusions (containing a slash)
                    if (strpos($excludedDir, '/') !== false) {
                        // Check if the current path contains the exclusion path
                        if (strpos($currentRelativePath, $excludedDir) !== false) {
                            $shouldExclude = true;
                            break;
                        }
                    }
                }
                
                if ($shouldExclude) {
                    continue;
                }
            }

            // Skip excluded files
            if (! $isDir && in_array($item, $excludedFiles)) {
                continue;
            }

            $filteredItems[] = $item;
        }

        // Sort items (directories first, then files)
        usort($filteredItems, function ($a, $b) use ($directory) {
            $aIsDir = is_dir($directory.'/'.$a);
            $bIsDir = is_dir($directory.'/'.$b);

            if ($aIsDir && ! $bIsDir) {
                return -1;
            } elseif (! $aIsDir && $bIsDir) {
                return 1;
            } else {
                return strcasecmp($a, $b);
            }
        });

        // Add each item to the tree
        $count = count($filteredItems);
        foreach ($filteredItems as $index => $item) {
            $path = $directory.'/'.$item;
            $isDir = is_dir($path);
            $isLast = ($index === $count - 1);

            // Create the prefix based on depth
            $prefix = '';
            for ($i = 0; $i < $depth; $i++) {
                $prefix .= '│   ';
            }

            // Add the item to the output
            $connector = $isLast ? '└── ' : '├── ';
            $output .= $prefix.$connector.$item."\n";

            // Recursively process subdirectories
            if ($isDir) {
                $childPrefix = '';
                for ($i = 0; $i < $depth; $i++) {
                    $childPrefix .= '│   ';
                }

                if ($isLast) {
                    $childPrefix .= '    ';
                } else {
                    $childPrefix .= '│   ';
                }

                $this->buildTree($path, $output, $depth + 1, $currentRelativePath);
            }
        }
    }

    /**
     * Generate a hash of the directory structure
     *
     * @param string $projectRoot The project root directory
     *
     * @return string The hash
     */
    private function generateDirectoryHash(string $projectRoot): string
    {
        $output = '';
        $this->buildTree($projectRoot, $output, 0, '');

        return md5($output);
    }
}
