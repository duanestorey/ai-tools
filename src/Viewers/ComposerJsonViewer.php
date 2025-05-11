<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Filesystem\Filesystem;

class ComposerJsonViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?string $lastHash = null;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function getName(): string
    {
        return 'Composer JSON';
    }

    public function isApplicable(string $projectRoot): bool
    {
        return $this->filesystem->exists($projectRoot.'/composer.json');
    }

    public function generate(string $projectRoot): string
    {
        $composerJsonPath = $projectRoot.'/composer.json';

        if (! $this->filesystem->exists($composerJsonPath)) {
            return "# Composer JSON\n\nNo composer.json file found in the project root.";
        }

        $content = file_get_contents($composerJsonPath);

        // Try to parse and pretty print JSON
        $json = json_decode($content);
        if ($json !== null) {
            $content = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "# Composer JSON\n\n```json\n".$content."\n```\n";
    }

    public function hasChanged(string $projectRoot): bool
    {
        $composerJsonPath = $projectRoot.'/composer.json';

        if (! $this->filesystem->exists($composerJsonPath)) {
            return false;
        }

        $hash = md5_file($composerJsonPath);

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }
}
