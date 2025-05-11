<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Finder\Finder;

class ReadmeViewer implements ViewerInterface
{
    private ?string $lastHash = null;

    public function __construct()
    {
        // No initialization needed
    }

    public function getName(): string
    {
        return 'README';
    }

    public function isApplicable(string $projectRoot): bool
    {
        $finder = new Finder;
        $finder->files()
            ->in($projectRoot)
            ->depth('== 0')
            ->name('/^readme(\.md)?$/i');

        return $finder->hasResults();
    }

    public function generate(string $projectRoot): string
    {
        $finder = new Finder;
        $finder->files()
            ->in($projectRoot)
            ->depth('== 0')
            ->name('/^readme(\.md)?$/i');

        if (! $finder->hasResults()) {
            return "# README\n\nNo README file found in the project root.";
        }

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());

            return "# README Content\n\n".$content;
        }

        return "# README\n\nError reading README file.";
    }

    public function hasChanged(string $projectRoot): bool
    {
        $finder = new Finder;
        $finder->files()
            ->in($projectRoot)
            ->depth('== 0')
            ->name('/^readme(\.md)?$/i');

        if (! $finder->hasResults()) {
            return false;
        }

        $hash = '';
        foreach ($finder as $file) {
            $hash = md5_file($file->getRealPath());
            break;
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }
}
