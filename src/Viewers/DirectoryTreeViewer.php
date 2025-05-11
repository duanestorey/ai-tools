<?php

namespace DuaneStorey\AiTools\Viewers;

use DuaneStorey\AiTools\Core\Configuration;

class DirectoryTreeViewer implements ViewerInterface
{
    private ?string $lastHash = null;

    private Configuration $config;

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
        $this->buildTree($projectRoot, $output, 0);
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
     */
    private function buildTree(string $directory, string &$output, int $depth): void
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

            // Skip excluded directories
            if ($isDir && in_array($item, $excludedDirs)) {
                continue;
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

                $this->buildTree($path, $output, $depth + 1);
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
        $this->buildTree($projectRoot, $output, 0);

        return md5($output);
    }
}
