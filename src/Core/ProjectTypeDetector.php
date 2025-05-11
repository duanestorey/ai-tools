<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class ProjectTypeDetector
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
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

        // Add more framework detections here in the future

        return $projectType;
    }

    /**
     * Check if the project is a Laravel project
     */
    private function isLaravelProject(string $projectRoot): bool
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
    private function detectLaravelVersion(string $projectRoot): ?string
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
}
