<?php

namespace DuaneStorey\AiTools\Core;

use DuaneStorey\AiTools\Viewers\ComposerJsonViewer;
use DuaneStorey\AiTools\Viewers\DirectoryTreeViewer;
use DuaneStorey\AiTools\Viewers\EnvVariablesViewer;
use DuaneStorey\AiTools\Viewers\GitInfoViewer;
use DuaneStorey\AiTools\Viewers\Laravel\RoutesViewer;
use DuaneStorey\AiTools\Viewers\Laravel\SchemaViewer;
use DuaneStorey\AiTools\Viewers\Rails\RoutesViewer as RailsRoutesViewer;
use DuaneStorey\AiTools\Viewers\Rails\SchemaViewer as RailsSchemaViewer;
use DuaneStorey\AiTools\Viewers\PackageJsonViewer;
use DuaneStorey\AiTools\Viewers\ProjectInfoViewer;
use DuaneStorey\AiTools\Viewers\ReadmeViewer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class OverviewGenerator
{
    private string $projectRoot;

    private SymfonyStyle $io;

    private Filesystem $filesystem;

    /** @var array<int, mixed> */
    private array $viewers = [];

    private ?ProjectType $projectType;

    private Configuration $config;

    public function __construct(string $projectRoot, SymfonyStyle $io)
    {
        $this->projectRoot = $projectRoot;
        $this->io = $io;
        $this->filesystem = new Filesystem;

        // Load configuration
        $this->config = new Configuration($projectRoot);

        // Detect project type
        $detector = new ProjectTypeDetector;
        $this->projectType = $detector->detect($projectRoot);

        // Register viewers
        $this->registerViewers();
    }

    /**
     * Register all available viewers
     */
    private function registerViewers(): void
    {
        $this->viewers = [];

        // Register core viewers based on configuration
        if ($this->config->isViewerEnabled('project_info')) {
            $this->viewers[] = new ProjectInfoViewer($this->projectType); // Always first to provide project context
        }

        if ($this->config->isViewerEnabled('directory_tree')) {
            $this->viewers[] = new DirectoryTreeViewer;
        }

        if ($this->config->isViewerEnabled('composer_json')) {
            $this->viewers[] = new ComposerJsonViewer;
        }

        if ($this->config->isViewerEnabled('package_json')) {
            $this->viewers[] = new PackageJsonViewer;
        }

        if ($this->config->isViewerEnabled('readme')) {
            $this->viewers[] = new ReadmeViewer;
        }

        if ($this->config->isViewerEnabled('git_info')) {
            $this->viewers[] = new GitInfoViewer;
        }

        if ($this->config->isViewerEnabled('env_variables')) {
            $this->viewers[] = new EnvVariablesViewer;
        }

        // Add Laravel-specific viewers if Laravel project is detected and enabled in config
        if ($this->projectType->hasTrait('laravel')) {
            if ($this->config->isViewerEnabled('laravel_routes')) {
                $this->viewers[] = new RoutesViewer($this->projectType);
            }

            if ($this->config->isViewerEnabled('laravel_schema')) {
                $this->viewers[] = new SchemaViewer($this->projectType);
            }
        }

        // Add Rails-specific viewers if Rails project is detected
        if ($this->projectType->hasTrait('rails')) {
            if ($this->config->isViewerEnabled('rails_routes')) {
                $this->viewers[] = new \DuaneStorey\AiTools\Viewers\Rails\RoutesViewer($this->projectType);
            }

            if ($this->config->isViewerEnabled('rails_schema')) {
                $this->viewers[] = new \DuaneStorey\AiTools\Viewers\Rails\SchemaViewer($this->projectType);
            }
        }

        // Additional framework-specific viewers can be added here in the future
    }

    /**
     * Generate the overview file
     */
    public function generate(): int
    {
        $outputFile = $this->config->get('output_file', 'ai-overview.md');
        $outputPath = $this->projectRoot.'/'.$outputFile;
        $content = '';
        $hasChanges = false;

        // Check if any viewers have changes
        foreach ($this->viewers as $index => $viewer) {
            if (! $viewer->isApplicable($this->projectRoot)) {
                continue;
            }

            if ($viewer->hasChanged($this->projectRoot)) {
                $hasChanges = true;
                break;
            }
        }

        // If no changes and file exists, we can skip generation
        if (! $hasChanges && $this->filesystem->exists($outputPath)) {
            $this->io->success('No changes detected. Overview file is up to date.');

            return Command::SUCCESS;
        }

        // Generate content from each viewer
        $stepNumber = 1;
        foreach ($this->viewers as $viewer) {
            $this->io->text(sprintf('<info>[%d]</info> <comment>Processing</comment> <options=bold>%s</options=bold>', $stepNumber++, $viewer->getName()));

            if ($viewer->isApplicable($this->projectRoot)) {
                $viewerContent = $viewer->generate($this->projectRoot);
                $content .= $viewerContent."\n";
            } else {
                // Add a placeholder section for non-applicable viewers
                $content .= "# {$viewer->getName()}\n\nNot applicable for this project.\n";
            }
        }

        // Write to file
        try {
            file_put_contents($outputPath, $content);
            $this->io->success(sprintf('Overview file generated: %s', $outputPath));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->io->error(sprintf('Failed to write overview file: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Watch for changes and regenerate when needed
     */
    public function watchAndGenerate(): int
    {
        // Initial generation
        $result = $this->generate();

        if ($result !== Command::SUCCESS) {
            return $result;
        }

        $this->io->text('Watching for changes...');

        // Store initial file state
        $fileHashes = $this->getFileHashes();

        // Watch for changes using a simple polling mechanism
        // Set up a maximum watch time (8 hours) to avoid infinite loop in static analysis
        $maxWatchTime = time() + (8 * 60 * 60);

        // Register signal handler for Ctrl+C if the function exists
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () {
                exit(0);
            });
        }

        // Watch for changes until max time is reached
        while (time() < $maxWatchTime) {
            // Sleep to reduce CPU usage
            sleep(1);

            // Process signals if the function exists
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            // Check for changes
            $newFileHashes = $this->getFileHashes();
            $changes = $this->detectChanges($fileHashes, $newFileHashes);

            if (! empty($changes)) {
                $this->io->text(sprintf('<info>%s</info> Changes detected, regenerating...', date('H:i:s')));

                foreach ($changes as $path => $type) {
                    $this->io->text(sprintf(' - <comment>%s</comment>: %s', $path, $type));
                }

                // Regenerate the overview file
                $this->generate();

                // Update file hashes
                $fileHashes = $newFileHashes;
            }
        }

        $this->io->warning('Maximum watch time reached. Exiting watch mode.');

        return Command::SUCCESS;
    }

    /**
     * Get hashes of all relevant files in the project
     *
     * @return array<string, string> Array of file paths and their hashes
     */
    private function getFileHashes(): array
    {
        $hashes = [];
        $this->scanDirectory($this->projectRoot, $hashes);

        return $hashes;
    }

    /**
     * Recursively scan a directory and calculate file hashes
     *
     * @param string                $directory Directory to scan
     * @param array<string, string> $hashes    Reference to array of hashes
     */
    private function scanDirectory(string $directory, array &$hashes): void
    {
        $items = scandir($directory);
        $excludedDirs = $this->config->get('excluded_directories', ['.git', 'vendor', 'node_modules']);
        $excludedFiles = $this->config->get('excluded_files', []);
        $outputFile = $this->config->get('output_file', 'ai-overview.md');

        // Always exclude the output file
        $excludedFiles[] = $outputFile;

        foreach ($items as $item) {
            // Skip dots and excluded directories/files
            if ($item === '.' || $item === '..' || in_array($item, $excludedDirs) || in_array($item, $excludedFiles)) {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                // Recursively scan subdirectories
                $this->scanDirectory($path, $hashes);
            } elseif (is_file($path)) {
                // Calculate hash for the file
                $hashes[$path] = md5_file($path).'-'.filemtime($path);
            }
        }
    }

    /**
     * Detect changes between two sets of file hashes
     *
     * @param array<string, string> $oldHashes
     * @param array<string, string> $newHashes
     *
     * @return array<string, string> Array of changed files and change types
     */
    private function detectChanges(array $oldHashes, array $newHashes): array
    {
        $changes = [];

        // Check for modified and deleted files
        foreach ($oldHashes as $path => $hash) {
            if (! isset($newHashes[$path])) {
                $changes[$path] = 'deleted';
            } elseif ($newHashes[$path] !== $hash) {
                $changes[$path] = 'modified';
            }
        }

        // Check for new files
        foreach ($newHashes as $path => $hash) {
            if (! isset($oldHashes[$path])) {
                $changes[$path] = 'created';
            }
        }

        return $changes;
    }
}
