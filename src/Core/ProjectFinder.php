<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class ProjectFinder
{
    /**
     * Find the project root directory
     *
     * @return string|null The project root path or null if not found
     */
    public function findProjectRoot(): ?string
    {
        $filesystem = new Filesystem;

        // Strategy 1: Try to find composer.json in current directory or parent directories
        $dir = getcwd();
        while ($dir !== '/' && $dir !== '') {
            if ($filesystem->exists($dir.'/composer.json')) {
                return $dir;
            }
            $dir = dirname($dir);
        }

        // Strategy 2: Try to use Composer's runtime API if available
        if (class_exists('\Composer\Factory')) {
            try {
                $composerFile = \Composer\Factory::getComposerFile();

                return dirname($composerFile);
            } catch (\Exception $e) {
                // Ignore exceptions from Composer
            }
        }

        // Strategy 3: Fall back to current directory
        return getcwd();
    }
}
