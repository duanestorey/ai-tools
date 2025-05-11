<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Filesystem\Filesystem;

class PackageJsonViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?string $lastHash = null;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function getName(): string
    {
        return 'Package JSON';
    }

    public function isApplicable(string $projectRoot): bool
    {
        return $this->filesystem->exists($projectRoot.'/package.json');
    }

    public function generate(string $projectRoot): string
    {
        $packageJsonPath = $projectRoot.'/package.json';

        if (! $this->filesystem->exists($packageJsonPath)) {
            return "# Package JSON\n\nNo package.json file found in the project root.";
        }

        $content = file_get_contents($packageJsonPath);

        // Try to parse and pretty print JSON
        $json = json_decode($content);
        if ($json !== null) {
            $content = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "# Package JSON\n\n```json\n".$content."\n```\n";
    }

    public function hasChanged(string $projectRoot): bool
    {
        $packageJsonPath = $projectRoot.'/package.json';

        if (! $this->filesystem->exists($packageJsonPath)) {
            return false;
        }

        $hash = md5_file($packageJsonPath);

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }
}
