<?php

namespace DuaneStorey\AiTools\Viewers\Rails;

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\ViewerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class RoutesViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?ProjectType $projectType;

    private ?string $lastHash = null;

    public function __construct(?ProjectType $projectType = null, ?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem;
        $this->projectType = $projectType;
    }

    /**
     * Get the filesystem instance
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Get file contents
     */
    public function getFileContents(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Create a new process
     *
     * @param array<string> $command          Command to run
     * @param string|null   $workingDirectory Working directory
     */
    public function createProcess(array $command, ?string $workingDirectory = null): Process
    {
        // Use array syntax for commands to avoid shell interpolation issues
        $process = new Process($command, $workingDirectory);
        $process->setTimeout(60); // Set a reasonable timeout
        
        return $process;
    }

    public function getName(): string
    {
        return 'Rails Routes';
    }

    public function isApplicable(string $projectRoot, ?ProjectType $projectType = null): bool
    {
        // Use the provided projectType if available, otherwise use the class property
        $type = $projectType ?? $this->projectType;

        if (! $type || ! $type->isRails()) {
            return false;
        }

        // Ensure filesystem is initialized
        if (! isset($this->filesystem)) {
            $this->filesystem = new Filesystem;
        }

        // Check if routes.rb exists
        return $this->filesystem->exists($projectRoot.'/config/routes.rb');
    }

    public function generate(string $projectRoot, ?ProjectType $projectType = null): string
    {
        // Store the projectType for future use if provided
        if ($projectType) {
            $this->projectType = $projectType;
        }

        if (! $this->isApplicable($projectRoot, $projectType)) {
            return '';
        }

        // Ensure filesystem is initialized
        if (! isset($this->filesystem)) {
            $this->filesystem = new Filesystem;
        }

        $output = "## Rails Routes\n\n";

        // Try to use the rails routes command if available
        $routesProcess = $this->createProcess(['bundle', 'exec', 'rails', 'routes'], $projectRoot);
        $routesProcess->run();

        if ($routesProcess->isSuccessful()) {
            // Process the output of rails routes command
            $routesOutput = $routesProcess->getOutput();
            $output .= "```\n".$routesOutput."\n```\n";
        } else {
            // Fallback to parsing routes.rb file
            $routesFile = $projectRoot.'/config/routes.rb';
            if ($this->filesystem->exists($routesFile)) {
                $routesContent = $this->getFileContents($routesFile);

                // Extract routes using regex patterns
                $output .= "## Routes from config/routes.rb\n\n";
                $output .= "```ruby\n".$routesContent."\n```\n\n";

                // Try to parse and format the routes
                $parsedRoutes = $this->parseRoutesFile($routesContent);
                if (! empty($parsedRoutes)) {
                    $output .= "## Parsed Routes\n\n";
                    $output .= "| HTTP Verb | Path | Controller#Action | Name |\n";
                    $output .= "|-----------|------|-------------------|------|\n";

                    foreach ($parsedRoutes as $route) {
                        $verb = $route['verb'] ?? '*';
                        $path = $route['path'] ?? '';
                        $action = $route['action'] ?? '';
                        $name = $route['name'] ?? '';

                        $output .= "| $verb | $path | $action | $name |\n";
                    }
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

        $routesFile = $projectRoot.'/config/routes.rb';
        $hash = '';

        if ($this->filesystem->exists($routesFile)) {
            $hash = md5_file($routesFile);
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }

    /**
     * Parse the routes.rb file to extract route information
     *
     * @return array<int, array<string, string>>
     */
    private function parseRoutesFile(string $routesContent): array
    {
        $routes = [];

        // Extract root route
        if (preg_match('/root\s+[\'"](\w+)#(\w+)[\'"]/', $routesContent, $rootMatch)) {
            $controller = $rootMatch[1];
            $action = $rootMatch[2];
            $routes[] = [
                'verb' => 'GET',
                'path' => '/',
                'action' => "$controller#$action",
                'name' => 'root',
            ];
        }

        // Extract resource routes
        preg_match_all('/resources\s+:(\w+)/', $routesContent, $resourceMatches);
        if (! empty($resourceMatches[1])) {
            foreach ($resourceMatches[1] as $resource) {
                // For each resource, create the standard RESTful routes
                $controller = $this->pluralToController($resource);

                $routes[] = ['verb' => 'GET', 'path' => "/$resource", 'action' => "$controller#index", 'name' => "${resource}_index"];
                $routes[] = ['verb' => 'GET', 'path' => "/$resource/new", 'action' => "$controller#new", 'name' => "new_${resource}"];
                $routes[] = ['verb' => 'POST', 'path' => "/$resource", 'action' => "$controller#create", 'name' => "${resource}_create"];
                $routes[] = ['verb' => 'GET', 'path' => "/$resource/:id", 'action' => "$controller#show", 'name' => "${resource}_show"];
                $routes[] = ['verb' => 'GET', 'path' => "/$resource/:id/edit", 'action' => "$controller#edit", 'name' => "edit_${resource}"];
                $routes[] = ['verb' => 'PATCH/PUT', 'path' => "/$resource/:id", 'action' => "$controller#update", 'name' => "${resource}_update"];
                $routes[] = ['verb' => 'DELETE', 'path' => "/$resource/:id", 'action' => "$controller#destroy", 'name' => "${resource}_destroy"];
            }
        }

        // Extract custom routes
        preg_match_all('/(?:get|post|put|patch|delete)\s+[\'"]([^\'"]+)[\'"],?\s+to:\s+[\'"](\w+)#(\w+)[\'"](?:,\s+as:\s+[\'"](\w+)[\'"])?/', $routesContent, $customMatches, PREG_SET_ORDER);

        foreach ($customMatches as $match) {
            $verb = strtoupper(preg_replace('/^.*?(get|post|put|patch|delete).*$/i', '$1', $match[0]));
            $path = $match[1];
            $controller = $match[2];
            $action = $match[3];
            $name = $match[4] ?? '';

            $routes[] = [
                'verb' => $verb,
                'path' => $path,
                'action' => "$controller#$action",
                'name' => $name,
            ];
        }

        return $routes;
    }

    /**
     * Convert a plural resource name to controller name
     */
    private function pluralToController(string $plural): string
    {
        // Simple pluralization rules - this could be expanded
        $controller = ucfirst($plural);

        // Check for common plural endings and convert to singular
        if (substr($plural, -3) === 'ies') {
            $controller = ucfirst(substr($plural, 0, -3).'y');
        } elseif (substr($plural, -1) === 's' && substr($plural, -2) !== 'ss') {
            $controller = ucfirst(substr($plural, 0, -1));
        }

        return $controller.'Controller';
    }
}
