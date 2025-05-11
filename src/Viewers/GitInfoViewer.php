<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class GitInfoViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?string $lastHash = null;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function getName(): string
    {
        return 'Git Information';
    }

    public function isApplicable(string $projectRoot): bool
    {
        return $this->filesystem->exists($projectRoot.'/.git');
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->isApplicable($projectRoot)) {
            return "# Git Information\n\nNo Git repository found in the project root.";
        }

        $output = "# Git Information\n\n";

        // Get repository information
        $repoInfoProcess = new Process(['git', 'config', '--get', 'remote.origin.url']);
        $repoInfoProcess->setWorkingDirectory($projectRoot);
        $repoInfoProcess->run();

        if ($repoInfoProcess->isSuccessful()) {
            $repoUrl = trim($repoInfoProcess->getOutput());
            if (! empty($repoUrl)) {
                // Clean up the URL to remove credentials if present
                $repoUrl = preg_replace('/https?:\/\/[^@]*@/', 'https://', $repoUrl);
                $output .= "## Repository URL\n\n`{$repoUrl}`\n\n";
            }
        }

        // Get all branches
        $branchesProcess = new Process(['git', 'branch', '--all']);
        $branchesProcess->setWorkingDirectory($projectRoot);
        $branchesProcess->run();

        if ($branchesProcess->isSuccessful()) {
            $branches = trim($branchesProcess->getOutput());
            if (! empty($branches)) {
                // Format branches for better readability
                $branchLines = explode("\n", $branches);
                $formattedBranches = array_map(function ($branch) {
                    return trim($branch);
                }, $branchLines);

                $output .= "## Branches\n\n```\n".implode("\n", $formattedBranches)."\n```\n\n";
            }
        }

        // Get Git configuration relevant to the project
        $configProcess = new Process(['git', 'config', '--list', '--local']);
        $configProcess->setWorkingDirectory($projectRoot);
        $configProcess->run();

        if ($configProcess->isSuccessful()) {
            $config = trim($configProcess->getOutput());
            if (! empty($config)) {
                // Filter out sensitive information
                $configLines = explode("\n", $config);
                $filteredConfig = array_filter($configLines, function ($line) {
                    // Exclude lines with potential credentials or tokens
                    return ! preg_match('/(password|token|secret|key|credential)/i', $line);
                });

                if (! empty($filteredConfig)) {
                    $output .= "## Git Configuration\n\n```\n".implode("\n", $filteredConfig)."\n```\n\n";
                }
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        if (! $this->isApplicable($projectRoot)) {
            return false;
        }

        // Check for new commits
        $hashProcess = new Process(['git', 'rev-parse', 'HEAD']);
        $hashProcess->setWorkingDirectory($projectRoot);
        $hashProcess->run();

        if (! $hashProcess->isSuccessful()) {
            return false;
        }

        $currentHash = trim($hashProcess->getOutput());

        if ($this->lastHash !== null && $this->lastHash === $currentHash) {
            return false;
        }

        $this->lastHash = $currentHash;

        return true;
    }
}
