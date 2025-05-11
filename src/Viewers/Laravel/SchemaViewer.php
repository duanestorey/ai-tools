<?php

namespace DuaneStorey\AiTools\Viewers\Laravel;

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\ViewerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class SchemaViewer implements ViewerInterface
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
        return 'Laravel Database Schema';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Only applicable for Laravel projects
        if ($this->projectType && ! $this->projectType->hasTrait('laravel')) {
            return false;
        }

        // Check if database/migrations directory exists
        return $this->filesystem->exists($projectRoot.'/database/migrations');
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->isApplicable($projectRoot)) {
            return "# Laravel Database Schema\n\nNot applicable for this project.";
        }

        $output = "# Laravel Database Schema\n\n";

        // Try to use schema:dump command if available (Laravel 8+)
        $schemaDumpProcess = new Process(['php', 'artisan', 'schema:dump', '--output']);
        $schemaDumpProcess->setWorkingDirectory($projectRoot);
        $schemaDumpProcess->run();

        if ($schemaDumpProcess->isSuccessful()) {
            $schemaDump = $schemaDumpProcess->getOutput();
            if (! empty($schemaDump)) {
                $output .= "## Database Schema (from schema:dump)\n\n";
                $output .= "```sql\n".$schemaDump."\n```\n\n";

                return $output;
            }
        }

        // If schema:dump fails, parse migration files
        $output .= "## Database Tables\n\n";
        $output .= "Extracted from migration files:\n\n";

        $tables = $this->extractTablesFromMigrations($projectRoot);

        if (empty($tables)) {
            $output .= "No database tables found in migration files.\n\n";
        } else {
            foreach ($tables as $tableName => $tableInfo) {
                $output .= "### Table: `{$tableName}`\n\n";

                if (! empty($tableInfo['columns']) && is_array($tableInfo['columns'])) {
                    $output .= "| Column | Type | Attributes |\n";
                    $output .= "|--------|------|------------|\n";

                    foreach ($tableInfo['columns'] as $column) {
                        if (is_array($column) && array_key_exists('name', $column) && array_key_exists('type', $column) && array_key_exists('attributes', $column)) {
                            $output .= "| {$column['name']} | {$column['type']} | {$column['attributes']} |\n";
                        }
                    }
                    $output .= "\n";
                }

                if (! empty($tableInfo['indexes']) && is_array($tableInfo['indexes'])) {
                    $output .= "**Indexes:**\n\n";
                    foreach ($tableInfo['indexes'] as $index) {
                        if (is_string($index)) {
                            $output .= "- {$index}\n";
                        }
                    }
                    $output .= "\n";
                }

                if (! empty($tableInfo['foreign_keys']) && is_array($tableInfo['foreign_keys'])) {
                    $output .= "**Foreign Keys:**\n\n";
                    foreach ($tableInfo['foreign_keys'] as $foreignKey) {
                        if (is_string($foreignKey)) {
                            $output .= "- {$foreignKey}\n";
                        }
                    }
                    $output .= "\n";
                }
            }
        }

        // Add information about models
        $output .= "## Eloquent Models\n\n";
        $models = $this->findEloquentModels($projectRoot);

        if (empty($models)) {
            $output .= "No Eloquent models found.\n\n";
        } else {
            $output .= "| Model | Table | Fillable | Relationships |\n";
            $output .= "|-------|-------|----------|---------------|\n";

            foreach ($models as $model) {
                $output .= "| {$model['name']} | {$model['table']} | {$model['fillable']} | {$model['relationships']} |\n";
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        $hash = '';

        // Check migrations directory
        $migrationsDir = $projectRoot.'/database/migrations';
        if ($this->filesystem->exists($migrationsDir)) {
            $finder = new Finder;
            $finder->files()->in($migrationsDir)->name('*.php');

            foreach ($finder as $file) {
                $hash .= md5_file($file->getRealPath());
            }
        }

        // Check models
        $modelsDir = $projectRoot.'/app/Models';
        if ($this->filesystem->exists($modelsDir)) {
            $finder = new Finder;
            $finder->files()->in($modelsDir)->name('*.php');

            foreach ($finder as $file) {
                $hash .= md5_file($file->getRealPath());
            }
        }

        // Legacy model location
        $legacyModelsDir = $projectRoot.'/app';
        if ($this->filesystem->exists($legacyModelsDir)) {
            $finder = new Finder;
            $finder->files()->in($legacyModelsDir)->depth('== 0')->name('*.php');

            foreach ($finder as $file) {
                $hash .= md5_file($file->getRealPath());
            }
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }

    /**
     * Extract table information from migration files
     */
    /**
     * Extract tables from migration files
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractTablesFromMigrations(string $projectRoot): array
    {
        $tables = [];
        $migrationsDir = $projectRoot.'/database/migrations';

        if (! $this->filesystem->exists($migrationsDir)) {
            return $tables;
        }

        $finder = new Finder;
        $finder->files()->in($migrationsDir)->name('*.php')->sortByName();

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());

            // Extract table name from create statements
            if (preg_match('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $tableName = $matches[1];

                if (! isset($tables[$tableName])) {
                    $tables[$tableName] = [
                        'columns' => [],
                        'indexes' => [],
                        'foreign_keys' => [],
                    ];
                }

                // Extract columns
                preg_match_all('/\$table->([a-zA-Z]+)\([\'"]([^\'"]+)[\'"](?:,\s*([^)]+))?\)(?:->[a-zA-Z]+\(\)?)*/', $content, $columnMatches, PREG_SET_ORDER);

                foreach ($columnMatches as $match) {
                    $type = $match[1];
                    $name = $match[2];
                    $params = isset($match[3]) ? $match[3] : '';

                    // Extract attributes (nullable, default, etc.)
                    $attributes = [];
                    if (preg_match('/->nullable\(\)/', $content, $nullableMatch)) {
                        $attributes[] = 'nullable';
                    }
                    if (preg_match('/->unique\(\)/', $content, $uniqueMatch)) {
                        $attributes[] = 'unique';
                    }
                    if (preg_match('/->default\(([^)]+)\)/', $content, $defaultMatch)) {
                        $attributes[] = 'default: '.$defaultMatch[1];
                    }

                    $tables[$tableName]['columns'][] = [
                        'name' => $name,
                        'type' => $type,
                        'attributes' => implode(', ', $attributes),
                    ];
                }

                // Extract indexes
                preg_match_all('/\$table->index\(\[[^\]]+\](?:,\s*[\'"][^\'"]+[\'"]\))?/', $content, $indexMatches);
                foreach ($indexMatches[0] as $indexMatch) {
                    $tables[$tableName]['indexes'][] = $indexMatch;
                }

                // Extract foreign keys
                preg_match_all('/\$table->foreign\([\'"]([^\'"]+)[\'"]\)(?:->references\([\'"]([^\'"]+)[\'"]\))?(?:->on\([\'"]([^\'"]+)[\'"]\))?/', $content, $foreignKeyMatches, PREG_SET_ORDER);
                foreach ($foreignKeyMatches as $match) {
                    $column = $match[1];
                    $references = isset($match[2]) ? $match[2] : 'id';
                    $on = isset($match[3]) ? $match[3] : '';

                    $tables[$tableName]['foreign_keys'][] = "{$column} references {$references} on {$on}";
                }
            }

            // Handle table alterations
            if (preg_match('/Schema::table\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $tableName = $matches[1];

                if (! isset($tables[$tableName])) {
                    $tables[$tableName] = [
                        'columns' => [],
                        'indexes' => [],
                        'foreign_keys' => [],
                    ];
                }

                // Extract added columns
                preg_match_all('/\$table->([a-zA-Z]+)\([\'"]([^\'"]+)[\'"](?:,\s*([^)]+))?\)(?:->[a-zA-Z]+\(\)?)*/', $content, $columnMatches, PREG_SET_ORDER);

                foreach ($columnMatches as $match) {
                    $type = $match[1];
                    $name = $match[2];
                    $params = isset($match[3]) ? $match[3] : '';

                    // Extract attributes (nullable, default, etc.)
                    $attributes = [];
                    if (preg_match('/->nullable\(\)/', $content, $nullableMatch)) {
                        $attributes[] = 'nullable';
                    }
                    if (preg_match('/->unique\(\)/', $content, $uniqueMatch)) {
                        $attributes[] = 'unique';
                    }
                    if (preg_match('/->default\(([^)]+)\)/', $content, $defaultMatch)) {
                        $attributes[] = 'default: '.$defaultMatch[1];
                    }

                    $tables[$tableName]['columns'][] = [
                        'name' => $name,
                        'type' => $type,
                        'attributes' => implode(', ', $attributes),
                    ];
                }
            }
        }

        return $tables;
    }

    /**
     * Find Eloquent models and extract information
     *
     * @return array<int, array<string, mixed>>
     */
    private function findEloquentModels(string $projectRoot): array
    {
        $models = [];

        // Check modern models location (Laravel 8+)
        $modelsDir = $projectRoot.'/app/Models';
        if ($this->filesystem->exists($modelsDir)) {
            $finder = new Finder;
            $finder->files()->in($modelsDir)->name('*.php');

            foreach ($finder as $file) {
                $model = $this->extractModelInfo($file->getRealPath());
                if ($model) {
                    $models[] = $model;
                }
            }
        }

        // Check legacy model location
        $legacyModelsDir = $projectRoot.'/app';
        if ($this->filesystem->exists($legacyModelsDir)) {
            $finder = new Finder;
            $finder->files()->in($legacyModelsDir)->depth('== 0')->name('*.php');

            foreach ($finder as $file) {
                $content = file_get_contents($file->getRealPath());

                // Only process if it extends Model
                if (strpos($content, 'extends Model') !== false) {
                    $model = $this->extractModelInfo($file->getRealPath());
                    if ($model) {
                        $models[] = $model;
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Extract model information from a file
     *
     * @return array<string, mixed>|null
     */
    private function extractModelInfo(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        $modelName = basename($filePath, '.php');

        // Skip if not a model
        if (strpos($content, 'extends Model') === false && strpos($content, 'Illuminate\Database\Eloquent\Model') === false) {
            return null;
        }

        // Extract table name
        $table = $this->snakeCase($modelName);
        if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $table = $matches[1];
        }

        // Extract fillable
        $fillable = '-';
        if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
            $fillableStr = $matches[1];
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $fillableStr, $fillableMatches);
            if (! empty($fillableMatches[1])) {
                $fillable = implode(', ', $fillableMatches[1]);
            }
        }

        // Extract relationships
        $relationships = [];

        // hasMany
        preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(\s*\)\s*\{[^}]*return\s+\$this->hasMany\s*\(\s*([^,\)]+)/', $content, $hasManyMatches, PREG_SET_ORDER);
        foreach ($hasManyMatches as $match) {
            $relationships[] = "hasMany: {$match[1]} ({$match[2]})";
        }

        // belongsTo
        preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(\s*\)\s*\{[^}]*return\s+\$this->belongsTo\s*\(\s*([^,\)]+)/', $content, $belongsToMatches, PREG_SET_ORDER);
        foreach ($belongsToMatches as $match) {
            $relationships[] = "belongsTo: {$match[1]} ({$match[2]})";
        }

        // belongsToMany
        preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(\s*\)\s*\{[^}]*return\s+\$this->belongsToMany\s*\(\s*([^,\)]+)/', $content, $belongsToManyMatches, PREG_SET_ORDER);
        foreach ($belongsToManyMatches as $match) {
            $relationships[] = "belongsToMany: {$match[1]} ({$match[2]})";
        }

        return [
            'name' => $modelName,
            'table' => $table,
            'fillable' => $fillable,
            'relationships' => ! empty($relationships) ? implode(', ', $relationships) : '-',
        ];
    }

    /**
     * Convert a string to snake_case
     */
    private function snakeCase(string $input): string
    {
        // Handle pluralization (very basic)
        $plural = $input;
        if (substr($input, -1) !== 's') {
            $plural = $input.'s';
        }

        $result = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $plural));

        return $result;
    }
}
