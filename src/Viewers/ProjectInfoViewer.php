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

        // Get framework version if available
        if ($this->projectType->hasTrait('laravel')) {
            $laravelVersion = $this->getFrameworkVersion($projectRoot, 'laravel');
            if ($laravelVersion) {
                $output .= "- **Laravel Version**: {$laravelVersion}\n";
            }
        }

        // Add PHP version
        $phpVersion = PHP_VERSION;
        $output .= "- **PHP Version**: {$phpVersion}\n";

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
     * @param string $projectRoot The project root directory
     * @param string $framework   The framework name (e.g., 'laravel')
     *
     * @return string|null The framework version or null if not found
     */
    private function getFrameworkVersion(string $projectRoot, string $framework): ?string
    {
        $composerLockPath = $projectRoot.'/composer.lock';
        $composerJsonPath = $projectRoot.'/composer.json';

        // First try to get the exact version from composer.lock
        if (file_exists($composerLockPath)) {
            $lockData = json_decode(file_get_contents($composerLockPath), true);
            if (isset($lockData['packages'])) {
                foreach ($lockData['packages'] as $package) {
                    if ($framework === 'laravel' && isset($package['name']) && $package['name'] === 'laravel/framework') {
                        return $package['version'] ?? null;
                    }
                }
            }
        }

        // If not found in lock file, check the required version in composer.json
        if (file_exists($composerJsonPath)) {
            $jsonData = json_decode(file_get_contents($composerJsonPath), true);
            if (isset($jsonData['require'])) {
                if ($framework === 'laravel' && isset($jsonData['require']['laravel/framework'])) {
                    return $jsonData['require']['laravel/framework'];
                }
            }
        }

        return null;
    }
}
