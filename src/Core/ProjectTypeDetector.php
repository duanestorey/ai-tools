<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class ProjectTypeDetector
{
    private Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem;
    }

    /**
     * Get the filesystem instance
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Get file contents
     */
    public function getFileContents(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Detect the project type
     */
    public function detect(string $projectRoot): ProjectType
    {
        $projectType = new ProjectType;

        // Always add the php trait
        $projectType->addTrait('php');

        // Check for Laravel
        if ($this->isLaravelProject($projectRoot)) {
            $projectType->addTrait('laravel');
            $laravelVersion = $this->detectLaravelVersion($projectRoot);
            if ($laravelVersion) {
                $projectType->setMetadata('laravel_version', $laravelVersion);
            }
        }

        // Check for Rails
        if ($this->isRailsProject($projectRoot)) {
            $projectType->addTrait('rails');
            $projectType->addTrait('ruby');
            $railsVersion = $this->detectRailsVersion($projectRoot);
            if ($railsVersion) {
                $projectType->setMetadata('rails_version', $railsVersion);
            }
        }

        // Add more framework detections here in the future

        return $projectType;
    }

    /**
     * Check if the project is a Laravel project
     */
    protected function isLaravelProject(string $projectRoot): bool
    {
        // Check for artisan file
        if ($this->filesystem->exists($projectRoot.'/artisan')) {
            return true;
        }

        // Check for app/Http/Controllers directory
        if ($this->filesystem->exists($projectRoot.'/app/Http/Controllers')) {
            return true;
        }

        // Check for Laravel dependency in composer.json
        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $composerJson = json_decode(file_get_contents($projectRoot.'/composer.json'), true);

            if (isset($composerJson['require']['laravel/framework']) ||
                isset($composerJson['require-dev']['laravel/framework'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect Laravel version
     */
    protected function detectLaravelVersion(string $projectRoot): ?string
    {
        // Try to get version from composer.json
        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $composerJson = json_decode(file_get_contents($projectRoot.'/composer.json'), true);

            if (isset($composerJson['require']['laravel/framework'])) {
                $versionConstraint = $composerJson['require']['laravel/framework'];
                // Extract version number from constraint (e.g., "^8.0" -> "8")
                if (preg_match('/\^?(\d+)/', $versionConstraint, $matches)) {
                    return $matches[1];
                }
            }
        }

        // Try to get version from composer.lock
        if ($this->filesystem->exists($projectRoot.'/composer.lock')) {
            $composerLock = json_decode(file_get_contents($projectRoot.'/composer.lock'), true);

            if (isset($composerLock['packages'])) {
                foreach ($composerLock['packages'] as $package) {
                    if ($package['name'] === 'laravel/framework') {
                        // Extract version number (e.g., "v8.83.27" -> "8")
                        if (preg_match('/v?(\d+)/', $package['version'], $matches)) {
                            return $matches[1];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if the project is a Rails project
     */
    protected function isRailsProject(string $projectRoot): bool
    {
        // Check for config/application.rb file (Rails specific)
        if ($this->filesystem->exists($projectRoot.'/config/application.rb')) {
            $content = $this->getFileContents($projectRoot.'/config/application.rb');
            if (strpos($content, 'Rails::Application') !== false) {
                return true;
            }
        }

        // Check for Gemfile with Rails
        if ($this->filesystem->exists($projectRoot.'/Gemfile')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile');
            if (preg_match('/gem\s+[\'\"](rails)[\'\"]/i', $content)) {
                return true;
            }
        }

        // Check for app/controllers directory (common in Rails)
        if ($this->filesystem->exists($projectRoot.'/app/controllers') &&
            $this->filesystem->exists($projectRoot.'/app/models')) {
            return true;
        }

        return false;
    }

    /**
     * Detect Rails version
     */
    protected function detectRailsVersion(string $projectRoot): ?string
    {
        // Try to get version from Gemfile
        if ($this->filesystem->exists($projectRoot.'/Gemfile')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile');

            // Look for gem 'rails', '~> X.Y.Z' or gem 'rails', 'X.Y.Z'
            if (preg_match('/gem\s+[\'\"](rails)[\'\"](\,\s*[\'\"](\~\>\s*)?(\d+))?/i', $content, $matches)) {
                return isset($matches[4]) ? $matches[4] : null; // Return major version number
            }
        }

        // Try to get version from Gemfile.lock
        if ($this->filesystem->exists($projectRoot.'/Gemfile.lock')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile.lock');

            // Look for rails (X.Y.Z) in the dependencies section
            if (preg_match('/rails\s+\((\d+)/', $content, $matches)) {
                return $matches[1]; // Return major version number
            }
        }

        return null;
    }
}
