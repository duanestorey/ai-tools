<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class Configuration
{
    /**
     * Default configuration values
     *
     * @var array<string, mixed>
     */
    private $defaults = [
        'output_file' => 'ai-overview.md',
        'excluded_directories' => ['.git', 'vendor', 'node_modules'],
        'excluded_files' => [],
        'directory_tree' => [
            'max_depth' => 4,
        ],
        'viewers' => [
            'project_info' => true,
            'directory_tree' => true,
            'composer_json' => true,
            'package_json' => true,
            'readme' => true,
            'git_info' => true,
            'env_variables' => true,
            'laravel_routes' => true,
            'laravel_schema' => true,
        ],
    ];

    /**
     * Current configuration values
     *
     * @var array<string, mixed>
     */
    private $config;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param string $projectRoot The project root directory
     */
    public function __construct(string $projectRoot)
    {
        $this->filesystem = new Filesystem;
        $this->config = $this->defaults;

        // Load project-specific configuration if it exists
        $this->loadProjectConfig($projectRoot);
    }

    /**
     * Load project-specific configuration
     *
     * @param string $projectRoot The project root directory
     */
    private function loadProjectConfig(string $projectRoot): void
    {
        $configFile = $projectRoot.'/.ai-tools.json';

        if ($this->filesystem->exists($configFile)) {
            $projectConfig = json_decode(file_get_contents($configFile), true);

            if (is_array($projectConfig)) {
                // Merge project configuration with defaults
                $this->config = $this->mergeConfig($this->defaults, $projectConfig);
            }
        }
    }

    /**
     * Recursively merge configuration arrays
     *
     * @param array<string, mixed> $defaults Default configuration
     * @param array<string, mixed> $override Override configuration
     *
     * @return array<string, mixed> Merged configuration
     */
    private function mergeConfig(array $defaults, array $override): array
    {
        $result = $defaults;

        foreach ($override as $key => $value) {
            // If the key exists in the defaults and both values are arrays, merge recursively
            if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = $this->mergeConfig($result[$key], $value);
            } else {
                // Otherwise, override the default value
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a configuration value
     *
     * @param string $key     Configuration key
     * @param mixed  $default Default value if key doesn't exist
     *
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Check if a viewer is enabled
     *
     * @param string $viewerName Viewer name
     *
     * @return bool Whether the viewer is enabled
     */
    public function isViewerEnabled(string $viewerName): bool
    {
        return (bool) $this->get("viewers.{$viewerName}", true);
    }

    /**
     * Get all configuration
     *
     * @return array<string, mixed> All configuration values
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Create an example configuration file in the project
     *
     * @param string $projectRoot The project root directory
     *
     * @return bool Whether the file was created
     */
    public function createExampleConfig(string $projectRoot): bool
    {
        $configFile = $projectRoot.'/.ai-tools.json';

        // Don't overwrite existing configuration
        if ($this->filesystem->exists($configFile)) {
            return false;
        }

        $exampleConfig = json_encode($this->defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($configFile, $exampleConfig);

        // Try to add to .gitignore if it exists
        $gitignorePath = $projectRoot.'/.gitignore';
        if ($this->filesystem->exists($gitignorePath)) {
            $gitignoreContent = file_get_contents($gitignorePath);
            if (strpos($gitignoreContent, '.ai-tools.json') === false) {
                file_put_contents($gitignorePath, $gitignoreContent."\n.ai-tools.json\n");
            }
        }

        return true;
    }
}
