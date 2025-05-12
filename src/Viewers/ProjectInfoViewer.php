<?php

namespace DuaneStorey\AiTools\Viewers;

use DuaneStorey\AiTools\Core\ProjectType;
use Symfony\Component\Filesystem\Filesystem;

class ProjectInfoViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?ProjectType $projectType;

    private ?string $lastHash = null;

    public function __construct(?ProjectType $projectType = null)
    {
        $this->filesystem = new Filesystem;
        $this->projectType = $projectType;
    }

    public function getName(): string
    {
        return 'Project Information';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Project info is always applicable
        return true;
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->projectType) {
            return "## Project Information\n\nNo project type information available.\n\n";
        }

        $output = "## Project Information\n\n";
        $output .= "- **Project Type**: {$this->projectType->getDescription()}\n";

        // Add traits if available
        $traits = $this->projectType->getTraits();
        if (! empty($traits)) {
            $output .= '- **Traits**: '.implode(', ', $traits)."\n";
        }
        
        // Customize output based on project type
        if ($this->projectType->hasTrait('laravel')) {
            // Laravel-specific information
            $laravelVersion = $this->getFrameworkVersion($projectRoot, 'laravel');
            if ($laravelVersion) {
                $output .= "- **Laravel Version**: {$laravelVersion}\n";
            }
            
            // Add PHP version for PHP-based projects
            $phpVersion = PHP_VERSION;
            $output .= "- **PHP Version**: {$phpVersion}\n";
        } elseif ($this->projectType->hasTrait('rails')) {
            // Rails-specific information
            $railsVersion = $this->projectType->getMetadata('rails_version');
            if ($railsVersion) {
                $output .= "- **Rails Version**: {$railsVersion}\n";
            }
            
            // Add Ruby version
            $rubyVersion = $this->detectRubyVersion($projectRoot);
            if ($rubyVersion) {
                $output .= "- **Ruby Version**: {$rubyVersion}\n";
            }
            
            // Check for React in package.json
            $reactVersion = $this->detectReactVersion($projectRoot);
            if ($reactVersion) {
                $output .= "- **React Version**: {$reactVersion}\n";
            }
        } else {
            // Default for other project types
            $phpVersion = PHP_VERSION;
            $output .= "- **PHP Version**: {$phpVersion}\n";
        }
        
        // Check for Node.js in all project types
        $nodeVersion = $this->detectNodeVersion($projectRoot);
        if ($nodeVersion) {
            $output .= "- **Node.js Version**: {$nodeVersion}\n";
        }
        
        // Check for React in all projects if not already shown for Rails
        if (!$this->projectType->hasTrait('rails')) {
            $reactVersion = $this->detectReactVersion($projectRoot);
            if ($reactVersion) {
                $output .= "- **React Version**: {$reactVersion}\n";
            }
        }

        // Add additional project metadata
        $composerJsonPath = $projectRoot.'/composer.json';
        if (file_exists($composerJsonPath)) {
            $composerData = json_decode(file_get_contents($composerJsonPath), true);
            if (isset($composerData['name'])) {
                $output .= "- **Project Name**: {$composerData['name']}\n";
            }
            if (isset($composerData['description'])) {
                $output .= "- **Description**: {$composerData['description']}\n";
            }
            if (isset($composerData['license'])) {
                $output .= "- **License**: {$composerData['license']}\n";
            }
        }

        // Add metadata from project type if available
        $metadata = $this->projectType->getAllMetadata();
        if (! empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $formattedKey = str_replace('_', ' ', $key);
                $formattedKey = ucwords($formattedKey);
                $output .= "- **{$formattedKey}**: {$value}\n";
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        // Calculate a hash based on composer.json and package.json
        $hash = '';

        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $hash .= md5_file($projectRoot.'/composer.json');
        }

        if ($this->filesystem->exists($projectRoot.'/package.json')) {
            $hash .= md5_file($projectRoot.'/package.json');
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }

    /**
     * Get the version of a framework from composer.json
     *
     * @param string $projectRoot
     * @param string $framework
     *
     * @return string|null The framework version or null if not found
     */
    private function getFrameworkVersion(string $projectRoot, string $framework): ?string
    {
        // First try to get the exact version from composer.lock
        $lockPath = $projectRoot.'/composer.lock';
        if (file_exists($lockPath)) {
            $lockData = json_decode(file_get_contents($lockPath), true);
            if (isset($lockData['packages'])) {
                foreach ($lockData['packages'] as $package) {
                    if (isset($package['name']) && $package['name'] === "$framework/framework") {
                        return $package['version'] ?? null;
                    }
                }
            }
        }

        // If not found in lock file, check the required version in composer.json
        $composerPath = $projectRoot.'/composer.json';
        if (file_exists($composerPath)) {
            $composerData = json_decode(file_get_contents($composerPath), true);
            if (isset($composerData['require']["$framework/framework"])) {
                return $composerData['require']["$framework/framework"];
            }
        }

        return null;
    }

    /**
     * Detect Ruby version from .ruby-version file or Gemfile
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectRubyVersion(string $projectRoot): ?string
    {
        // Check .ruby-version file first
        $rubyVersionFile = $projectRoot.'/.ruby-version';
        if (file_exists($rubyVersionFile)) {
            $version = trim(file_get_contents($rubyVersionFile));
            if ($version) {
                return $version;
            }
        }
        
        // Check Gemfile for ruby version
        $gemfilePath = $projectRoot.'/Gemfile';
        if (file_exists($gemfilePath)) {
            $gemfileContent = file_get_contents($gemfilePath);
            if (preg_match('/ruby\s+[\"](\d+\.\d+\.\d+)[\"]/i', $gemfileContent, $matches)) {
                return $matches[1];
            }
            if (preg_match('/ruby\s+[\"](\d+\.\d+)[\"]/i', $gemfileContent, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Detect React version from package.json
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectReactVersion(string $projectRoot): ?string
    {
        $packageJsonPath = $projectRoot.'/package.json';
        if (file_exists($packageJsonPath)) {
            $packageData = json_decode(file_get_contents($packageJsonPath), true);
            
            // Check for React in dependencies
            if (isset($packageData['dependencies']['react'])) {
                return $this->cleanVersionString($packageData['dependencies']['react']);
            }
            
            // Check for React in devDependencies
            if (isset($packageData['devDependencies']['react'])) {
                return $this->cleanVersionString($packageData['devDependencies']['react']);
            }
            
            // Check for React-related dependencies that indicate React is being used
            $reactRelatedPackages = [
                'react-dom', 'react-router', 'react-router-dom', 'next', 'gatsby',
                '@remix-run/react', '@vitejs/plugin-react', 'vite-plugin-react'
            ];
            
            foreach ($reactRelatedPackages as $package) {
                if (isset($packageData['dependencies'][$package]) || isset($packageData['devDependencies'][$package])) {
                    // If we found a React-related package but not React itself, it's likely React is being used
                    // but we don't know the exact version
                    return 'Used (version unknown)';
                }
            }
            
            // Check for React scripts
            if (isset($packageData['scripts'])) {
                foreach ($packageData['scripts'] as $script => $command) {
                    if (strpos($command, 'react-scripts') !== false) {
                        return 'Used (version unknown)';
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Detect Node.js version from .nvmrc or package.json
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectNodeVersion(string $projectRoot): ?string
    {
        // Check .nvmrc file first
        $nvmrcPath = $projectRoot.'/.nvmrc';
        if (file_exists($nvmrcPath)) {
            $version = trim(file_get_contents($nvmrcPath));
            if ($version) {
                return $version;
            }
        }
        
        // Check package.json engines
        $packageJsonPath = $projectRoot.'/package.json';
        if (file_exists($packageJsonPath)) {
            $packageData = json_decode(file_get_contents($packageJsonPath), true);
            
            // Try to get version from engines.node
            if (isset($packageData['engines']['node'])) {
                return $this->cleanVersionString($packageData['engines']['node']);
            }
            
            // If package.json exists but no specific version is found,
            // check if we can determine the Node.js version from the environment
            if ($this->isNodeProject($packageJsonPath)) {
                // Try to get the Node.js version from the system
                $nodeVersionProcess = new \Symfony\Component\Process\Process(['node', '-v']);
                $nodeVersionProcess->run();
                
                if ($nodeVersionProcess->isSuccessful()) {
                    $nodeVersion = trim($nodeVersionProcess->getOutput());
                    // Remove 'v' prefix if present
                    return ltrim($nodeVersion, 'v');
                }
                
                // If we can't get the actual version, at least indicate Node.js is used
                return 'Used (version unknown)';
            }
        }
        
        return null;
    }
    
    /**
     * Determine if this is a Node.js project based on package.json
     *
     * @param string $packageJsonPath
     * @return bool
     */
    private function isNodeProject(string $packageJsonPath): bool
    {
        if (!file_exists($packageJsonPath)) {
            return false;
        }
        
        $packageData = json_decode(file_get_contents($packageJsonPath), true);
        
        // Check if any of these common Node.js indicators are present
        $nodeIndicators = [
            'dependencies', 'devDependencies', 'scripts', 'engines',
            'main', 'type', 'bin', 'module'
        ];
        
        foreach ($nodeIndicators as $indicator) {
            if (isset($packageData[$indicator]) && !empty($packageData[$indicator])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Clean version string by removing ^, ~, etc.
     *
     * @param string $version
     * @return string
     */
    private function cleanVersionString(string $version): string
    {
        return preg_replace('/[^\d\.]/i', '', $version);
    }
}
