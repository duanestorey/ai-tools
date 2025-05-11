<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Filesystem\Filesystem;

class EnvVariablesViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?string $lastHash = null;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function getName(): string
    {
        return 'Environment Variables';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Check for .env, .env.example, or .env.sample files
        return $this->filesystem->exists($projectRoot.'/.env') ||
               $this->filesystem->exists($projectRoot.'/.env.example') ||
               $this->filesystem->exists($projectRoot.'/.env.sample');
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->isApplicable($projectRoot)) {
            return "# Environment Variables\n\nNo environment files found in the project root.";
        }

        $output = "# Environment Variables\n\n";
        $output .= "> Note: Only environment variable keys are shown, values are omitted for security reasons.\n\n";

        // Check for .env.example or .env.sample first (preferred as they don't contain real values)
        $envFiles = [
            '.env.example' => 'Example Environment Variables',
            '.env.sample' => 'Sample Environment Variables',
            '.env' => 'Current Environment Variables',
        ];

        foreach ($envFiles as $file => $title) {
            $filePath = $projectRoot.'/'.$file;

            if ($this->filesystem->exists($filePath)) {
                $output .= "## {$title}\n\n";
                $output .= $this->parseEnvFile($filePath);
                $output .= "\n";
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        $hash = '';

        // Check all potential env files
        $envFiles = ['.env', '.env.example', '.env.sample'];

        foreach ($envFiles as $file) {
            $filePath = $projectRoot.'/'.$file;

            if ($this->filesystem->exists($filePath)) {
                $hash .= md5_file($filePath);
            }
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }

    /**
     * Parse an environment file and extract keys and comments
     */
    private function parseEnvFile(string $filePath): string
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $output = "```\n";
        $currentSection = null;

        foreach ($lines as $line) {
            // Skip empty lines
            if (trim($line) === '') {
                $output .= "\n";

                continue;
            }

            // Keep comments as they provide context
            if (strpos($line, '#') === 0) {
                $output .= $line."\n";

                continue;
            }

            // Extract key from KEY=value format
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $output .= $key."=<value omitted>\n";
            } else {
                // If not a key=value format, include the line as is
                $output .= $line."\n";
            }
        }

        $output .= "```\n";

        return $output;
    }
}
