<?php

namespace DuaneStorey\AiTools\Viewers\Laravel;

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\ViewerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class RoutesViewer implements ViewerInterface
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
        return 'Laravel Routes';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Only applicable for Laravel projects
        if ($this->projectType && ! $this->projectType->hasTrait('laravel')) {
            return false;
        }

        // Check if artisan exists
        return $this->filesystem->exists($projectRoot.'/artisan');
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->isApplicable($projectRoot)) {
            return "# Laravel Routes\n\nNot applicable for this project.";
        }

        $output = "# Laravel Routes\n\n";

        // Try to get routes using artisan command
        $routesProcess = new Process(['php', 'artisan', 'route:list', '--json']);
        $routesProcess->setWorkingDirectory($projectRoot);
        $routesProcess->run();

        if ($routesProcess->isSuccessful()) {
            $routesJson = $routesProcess->getOutput();
            $routes = json_decode($routesJson, true);

            if (is_array($routes) && ! empty($routes)) {
                $output .= "## API Routes\n\n";
                $output .= "| Method | URI | Name | Controller |\n";
                $output .= "|--------|-----|------|------------|\n";

                $apiRoutes = array_filter($routes, function ($route) {
                    return strpos($route['uri'], 'api/') === 0;
                });

                foreach ($apiRoutes as $route) {
                    $method = $route['method'];
                    $uri = $route['uri'];
                    $name = $route['name'] ?? '-';
                    $action = $route['action'] ?? '-';

                    // Clean up action for display
                    if (is_string($action) && strpos($action, 'App\\Http\\Controllers\\') === 0) {
                        $action = str_replace('App\\Http\\Controllers\\', '', $action);
                    }

                    $output .= "| {$method} | {$uri} | {$name} | {$action} |\n";
                }

                $output .= "\n## Web Routes\n\n";
                $output .= "| Method | URI | Name | Controller |\n";
                $output .= "|--------|-----|------|------------|\n";

                $webRoutes = array_filter($routes, function ($route) {
                    return strpos($route['uri'], 'api/') !== 0;
                });

                foreach ($webRoutes as $route) {
                    $method = $route['method'];
                    $uri = $route['uri'];
                    $name = $route['name'] ?? '-';
                    $action = $route['action'] ?? '-';

                    // Clean up action for display
                    if (is_string($action) && strpos($action, 'App\\Http\\Controllers\\') === 0) {
                        $action = str_replace('App\\Http\\Controllers\\', '', $action);
                    }

                    $output .= "| {$method} | {$uri} | {$name} | {$action} |\n";
                }
            } else {
                $output .= "No routes defined in this Laravel application.\n\n";
            }
        } else {
            // If artisan command fails, try to parse routes from the routes files
            $output .= "Could not retrieve routes using artisan command. Parsing routes files instead.\n\n";

            $routesFiles = [
                'routes/web.php' => 'Web Routes',
                'routes/api.php' => 'API Routes',
            ];

            foreach ($routesFiles as $file => $title) {
                $filePath = $projectRoot.'/'.$file;

                if ($this->filesystem->exists($filePath)) {
                    $output .= "## {$title}\n\n";
                    $output .= "```php\n";
                    $output .= file_get_contents($filePath);
                    $output .= "\n```\n\n";
                }
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        $hash = '';

        // Check routes files
        $routesFiles = [
            'routes/web.php',
            'routes/api.php',
            'routes/channels.php',
            'routes/console.php',
        ];

        foreach ($routesFiles as $file) {
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
}
