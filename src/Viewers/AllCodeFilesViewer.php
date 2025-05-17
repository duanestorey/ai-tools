<?php

namespace DuaneStorey\AiTools\Viewers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class AllCodeFilesViewer implements ViewerInterface
{
    /**
     * List of file extensions to include
     *
     * @var array<string>
     */
    private array $codeFileExtensions = [
        // PHP/Laravel
        '.php', '.blade.php',
        
        // JavaScript assets (needed for both Laravel and Rails)
        '.js', '.jsx', '.ts', '.tsx',
        
        // Styles (needed for both Laravel and Rails)
        '.css', '.scss', '.sass',
        
        // Ruby/Rails
        '.rb', '.rake', '.gemspec', '.ru', '.erb',
        '.haml', '.slim',
        
        // Config files (for both)
        '.yml', '.yaml', '.json', '.env',
        
        // Shell scripts (for both)
        '.sh', '.bash'
    ];

    /**
     * Get the name of this viewer
     */
    public function getName(): string
    {
        return 'All Code Files';
    }

    /**
     * Check if this viewer is applicable for the project
     *
     * @param string $projectRoot The path to the project root
     */
    public function isApplicable(string $projectRoot): bool
    {
        return true; // This viewer is always applicable
    }

    /**
     * Check if content has changed since the last generation
     *
     * @param string $projectRoot The path to the project root
     */
    public function hasChanged(string $projectRoot): bool
    {
        return true; // Always regenerate to ensure all code files are up to date
    }

    /**
     * Generate the content for this viewer
     *
     * @param string $projectRoot The path to the project root
     */
    public function generate(string $projectRoot): string
    {
        $output = "# All Code Files\n\n";
        $output .= "This section contains all code files in the project.\n\n";
        
        $excludedDirs = $this->getExcludedDirectories($projectRoot);
        $excludedFiles = $this->getExcludedFiles($projectRoot);
        
        // Get all files
        $files = $this->getAllCodeFiles($projectRoot, $excludedDirs, $excludedFiles);
        
        if (empty($files)) {
            return $output . "No code files found in the project.\n";
        }
        
        // Sort files by path
        ksort($files);
        
        // Add files to output
        foreach ($files as $relativePath => $content) {
            $output .= "## File: {$relativePath}\n\n";
            $output .= "```\n{$content}\n```\n\n";
        }
        
        return $output;
    }
    
    /**
     * Get a list of directories to exclude from the configuration
     *
     * @param string $projectRoot The path to the project root
     * @return array<string>
     */
    private function getExcludedDirectories(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        $defaultExcludedDirs = ['.git', 'vendor', 'node_modules'];
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['excluded_directories']) && is_array($config['excluded_directories'])) {
                return $config['excluded_directories'];
            }
        }
        
        return $defaultExcludedDirs;
    }
    
    /**
     * Get a list of files to exclude from the configuration
     *
     * @param string $projectRoot The path to the project root
     * @return array<string>
     */
    private function getExcludedFiles(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        $defaultExcludedFiles = ['.env'];
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['excluded_files']) && is_array($config['excluded_files'])) {
                return $config['excluded_files'];
            }
        }
        
        return $defaultExcludedFiles;
    }
    
    /**
     * Get all code files in the project
     *
     * @param string $projectRoot The path to the project root
     * @param array<string> $excludedDirs Directories to exclude
     * @param array<string> $excludedFiles Files to exclude
     * @return array<string, string> Array of file paths and their contents
     */
    private function getAllCodeFiles(string $projectRoot, array $excludedDirs, array $excludedFiles): array
    {
        $files = [];
        
        // Check for custom file extensions in configuration
        $codeFileExtensions = $this->getCustomFileExtensions($projectRoot);
        echo "\nUsing extensions: " . implode(', ', $codeFileExtensions) . "\n\n";
        
        // Get output file name to exclude it
        $configFile = $projectRoot . '/.ai-tools.json';
        $outputFile = 'ai-overview.md';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['output_file'])) {
                $outputFile = $config['output_file'];
            }
        }
        
        // Also exclude the "-all" version of the file
        $pathInfo = pathinfo($outputFile);
        $allOutputFile = $pathInfo['filename'] . '-all.' . ($pathInfo['extension'] ?? 'md');
        
        // Add output files to excluded files list
        $excludedFiles[] = $outputFile;
        $excludedFiles[] = $allOutputFile;
        
        echo "Excluding output files: $outputFile, $allOutputFile\n";
        
        $directory = new RecursiveDirectoryIterator(
            $projectRoot,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
        );
        
        $iterator = new RecursiveIteratorIterator($directory);
        
        // Track which files are included
        $includeCount = 0;
        $excludeCount = 0;
        $includeList = [];
        
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            // Skip directories
            if ($file->isDir()) {
                continue;
            }
            
            $relativePath = $this->getRelativePath($file->getPathname(), $projectRoot);
            
            // Skip files in excluded directories
            foreach ($excludedDirs as $excludedDir) {
                $excludedDirPath = trim($excludedDir, '/');
                if (strpos($relativePath, $excludedDirPath . '/') === 0 || $relativePath === $excludedDirPath) {
                    continue 2; // Skip to the next file
                }
            }
            
            // Skip excluded files
            foreach ($excludedFiles as $excludedFile) {
                if ($relativePath === $excludedFile || basename($relativePath) === $excludedFile) {
                    continue 2; // Skip to the next file
                }
            }
            
            // Get file extension
            $extension = strtolower('.' . $file->getExtension());
            
            // Strict extension check - extensions must match exactly
            $isValidExtension = false;
            foreach ($codeFileExtensions as $validExt) {
                if ($extension === $validExt) {
                    $isValidExtension = true;
                    break;
                }
            }
            
            if (!$isValidExtension) {
                $excludeCount++;
                continue;
            }
            
            // Add file to the list
            $files[$relativePath] = file_get_contents($file->getPathname());
            $includeCount++;
            $includeList[] = "{$relativePath} ({$extension})";
        }
        
        echo "Included $includeCount files, excluded $excludeCount files based on extension filtering.\n";
        echo "Included files: " . implode(', ', $includeList) . "\n";
        
        return $files;
    }
    
    /**
     * Get the relative path of a file
     *
     * @param string $path The absolute path
     * @param string $root The project root
     * @return string The relative path
     */
    private function getRelativePath(string $path, string $root): string
    {
        return ltrim(str_replace($root, '', $path), '/');
    }
    
    /**
     * Get custom file extensions from configuration or use defaults
     *
     * @param string $projectRoot The path to the project root
     * @return array<string> Array of file extensions
     */
    private function getCustomFileExtensions(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['code_files']['extensions']) && is_array($config['code_files']['extensions'])) {
                // Found custom extensions in config, use them
                $extensions = $config['code_files']['extensions'];
                // Log this to stdout for debugging
                echo PHP_EOL . "Using custom file extensions: " . implode(', ', $extensions) . PHP_EOL;
                return $extensions;
            }
        }
        
        // Use default extensions
        echo PHP_EOL . "Using default file extensions" . PHP_EOL;
        return $this->codeFileExtensions;
    }
} 