<?php

namespace DuaneStorey\AiTools\Viewers\Rails;

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\ViewerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class SchemaViewer implements ViewerInterface
{
    private Filesystem $filesystem;
    private ?ProjectType $projectType;
    private ?string $lastHash = null;

    public function __construct(?ProjectType $projectType = null)
    {
        $this->filesystem = new Filesystem();
        $this->projectType = $projectType;
    }

    public function getName(): string
    {
        return 'Rails Database Schema';
    }

    public function isApplicable(string $projectRoot): bool
    {
        if (!$this->projectType || !$this->projectType->isRails()) {
            return false;
        }

        // Check if schema.rb or structure.sql exists
        return $this->filesystem->exists($projectRoot . '/db/schema.rb') || 
               $this->filesystem->exists($projectRoot . '/db/structure.sql');
    }

    public function generate(string $projectRoot): string
    {
        if (!$this->isApplicable($projectRoot)) {
            return "# Rails Database Schema\n\nNo Rails database schema found in this project.";
        }

        $output = "# Rails Database Schema\n\n";

        // Check for schema.rb first
        $schemaFile = $projectRoot . '/db/schema.rb';
        if ($this->filesystem->exists($schemaFile)) {
            $tables = $this->extractTablesFromSchema($schemaFile);
            $output .= $this->formatTablesOutput($tables);
        } else {
            // Try structure.sql if schema.rb doesn't exist
            $structureFile = $projectRoot . '/db/structure.sql';
            if ($this->filesystem->exists($structureFile)) {
                $tables = $this->extractTablesFromStructure($structureFile);
                $output .= $this->formatTablesOutput($tables);
            }
        }

        // Extract model information
        $models = $this->findRailsModels($projectRoot);
        if (!empty($models)) {
            $output .= "\n## Model Relationships\n\n";
            
            foreach ($models as $model) {
                $output .= "### " . $model['name'] . "\n\n";
                
                if (!empty($model['associations'])) {
                    $output .= "**Associations:**\n\n";
                    foreach ($model['associations'] as $association) {
                        $output .= "- " . $association . "\n";
                    }
                    $output .= "\n";
                }
                
                if (!empty($model['validations'])) {
                    $output .= "**Validations:**\n\n";
                    foreach ($model['validations'] as $validation) {
                        $output .= "- " . $validation . "\n";
                    }
                    $output .= "\n";
                }
                
                if (!empty($model['scopes'])) {
                    $output .= "**Scopes:**\n\n";
                    foreach ($model['scopes'] as $scope) {
                        $output .= "- " . $scope . "\n";
                    }
                    $output .= "\n";
                }
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        if (!$this->isApplicable($projectRoot)) {
            return false;
        }

        $hash = '';
        
        // Check schema.rb
        $schemaFile = $projectRoot . '/db/schema.rb';
        if ($this->filesystem->exists($schemaFile)) {
            $hash .= md5_file($schemaFile);
        }
        
        // Check structure.sql
        $structureFile = $projectRoot . '/db/structure.sql';
        if ($this->filesystem->exists($structureFile)) {
            $hash .= md5_file($structureFile);
        }
        
        // Check models
        $modelsDir = $projectRoot . '/app/models';
        if ($this->filesystem->exists($modelsDir)) {
            $finder = new Finder();
            $finder->files()->in($modelsDir)->name('*.rb');
            
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
     * Extract tables from schema.rb file
     * 
     * @param string $schemaFile
     * @return array<string, array<string, mixed>>
     */
    private function extractTablesFromSchema(string $schemaFile): array
    {
        $tables = [];
        $content = file_get_contents($schemaFile);
        
        // Extract table definitions
        preg_match_all('/create_table\s+"([^"]+)"(?:,\s*(?:id:\s*([^,]+)|force:\s*([^,]+)|\w+:\s*[^,]+))*\s+do\s+\|t\|(.*?)end/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableContent = $match[4];
            
            $tables[$tableName] = [
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => []
            ];
            
            // Extract columns
            preg_match_all('/t\.(\w+)\s+"([^"]+)"(?:,\s*([^,]+))?/', $tableContent, $columnMatches, PREG_SET_ORDER);
            foreach ($columnMatches as $columnMatch) {
                $columnType = $columnMatch[1];
                $columnName = $columnMatch[2];
                $columnOptions = $columnMatch[3] ?? '';
                
                $tables[$tableName]['columns'][] = [
                    'name' => $columnName,
                    'type' => $columnType,
                    'attributes' => $columnOptions
                ];
            }
            
            // Extract indexes
            preg_match_all('/add_index\s+"' . preg_quote($tableName, '/') . '",\s+\[([^\]]+)\](?:,\s*([^,]+))?/', $content, $indexMatches, PREG_SET_ORDER);
            foreach ($indexMatches as $indexMatch) {
                $indexColumns = $indexMatch[1];
                $indexOptions = $indexMatch[2] ?? '';
                $tables[$tableName]['indexes'][] = "Index on ($indexColumns) $indexOptions";
            }
            
            // Extract foreign keys
            preg_match_all('/add_foreign_key\s+"' . preg_quote($tableName, '/') . '",\s+"([^"]+)"(?:,\s*([^,]+))?/', $content, $fkMatches, PREG_SET_ORDER);
            foreach ($fkMatches as $fkMatch) {
                $referencedTable = $fkMatch[1];
                $fkOptions = $fkMatch[2] ?? '';
                $tables[$tableName]['foreign_keys'][] = "References $referencedTable $fkOptions";
            }
        }
        
        return $tables;
    }

    /**
     * Extract tables from structure.sql file
     * 
     * @param string $structureFile
     * @return array<string, array<string, mixed>>
     */
    private function extractTablesFromStructure(string $structureFile): array
    {
        $tables = [];
        $content = file_get_contents($structureFile);
        
        // Extract CREATE TABLE statements
        preg_match_all('/CREATE TABLE\s+([^\s(]+)\s*\((.*?)\);/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $tableName = $match[1];
            // Remove backticks or quotes if present
            $tableName = preg_replace('/[`"\']/', '', $tableName);
            
            $tableContent = $match[2];
            
            $tables[$tableName] = [
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => []
            ];
            
            // Extract columns
            preg_match_all('/\s*`?([^`\s]+)`?\s+([^\s,]+)([^,]*),?/s', $tableContent, $columnMatches, PREG_SET_ORDER);
            foreach ($columnMatches as $columnMatch) {
                $columnName = $columnMatch[1];
                $columnType = $columnMatch[2];
                $columnOptions = $columnMatch[3] ?? '';
                
                // Skip if this is actually an index or constraint
                if (preg_match('/^(PRIMARY|KEY|CONSTRAINT|INDEX|UNIQUE)/i', $columnName)) {
                    continue;
                }
                
                $tables[$tableName]['columns'][] = [
                    'name' => $columnName,
                    'type' => $columnType,
                    'attributes' => trim($columnOptions)
                ];
            }
            
            // Extract indexes
            preg_match_all('/\s*(KEY|INDEX|UNIQUE)\s+`?([^`\s(]+)`?\s*\(([^)]+)\)/i', $tableContent, $indexMatches, PREG_SET_ORDER);
            foreach ($indexMatches as $indexMatch) {
                $indexType = $indexMatch[1];
                $indexName = $indexMatch[2];
                $indexColumns = $indexMatch[3];
                $tables[$tableName]['indexes'][] = "$indexType $indexName on ($indexColumns)";
            }
            
            // Extract primary keys
            preg_match_all('/\s*PRIMARY KEY\s*\(([^)]+)\)/i', $tableContent, $pkMatches, PREG_SET_ORDER);
            foreach ($pkMatches as $pkMatch) {
                $pkColumns = $pkMatch[1];
                $tables[$tableName]['indexes'][] = "PRIMARY KEY on ($pkColumns)";
            }
        }
        
        // Extract foreign keys (might be at the end of the file)
        preg_match_all('/ALTER TABLE\s+([^\s]+)\s+ADD\s+CONSTRAINT\s+([^\s]+)\s+FOREIGN KEY\s*\(([^)]+)\)\s+REFERENCES\s+([^\s(]+)\s*\(([^)]+)\)([^;]*);/i', $content, $fkMatches, PREG_SET_ORDER);
        foreach ($fkMatches as $fkMatch) {
            $tableName = preg_replace('/[`"\']/', '', $fkMatch[1]);
            $constraintName = $fkMatch[2];
            $columns = $fkMatch[3];
            $referencedTable = preg_replace('/[`"\']/', '', $fkMatch[4]);
            $referencedColumns = $fkMatch[5];
            $options = $fkMatch[6] ?? '';
            
            if (isset($tables[$tableName])) {
                $tables[$tableName]['foreign_keys'][] = "CONSTRAINT $constraintName FOREIGN KEY ($columns) REFERENCES $referencedTable ($referencedColumns) $options";
            }
        }
        
        return $tables;
    }

    /**
     * Format tables data into markdown output
     * 
     * @param array<string, array<string, mixed>> $tables
     * @return string
     */
    private function formatTablesOutput(array $tables): string
    {
        $output = "## Database Tables\n\n";
        
        foreach ($tables as $tableName => $tableInfo) {
            $output .= "### Table: `{$tableName}`\n\n";
            
            if (!empty($tableInfo['columns']) && is_array($tableInfo['columns'])) {
                $output .= "| Column | Type | Attributes |\n";
                $output .= "|--------|------|------------|\n";
                
                foreach ($tableInfo['columns'] as $column) {
                    if (is_array($column) && array_key_exists('name', $column) && array_key_exists('type', $column) && array_key_exists('attributes', $column)) {
                        $output .= "| {$column['name']} | {$column['type']} | {$column['attributes']} |\n";
                    }
                }
                $output .= "\n";
            }
            
            if (!empty($tableInfo['indexes']) && is_array($tableInfo['indexes'])) {
                $output .= "**Indexes:**\n\n";
                foreach ($tableInfo['indexes'] as $index) {
                    if (is_string($index)) {
                        $output .= "- {$index}\n";
                    }
                }
                $output .= "\n";
            }
            
            if (!empty($tableInfo['foreign_keys']) && is_array($tableInfo['foreign_keys'])) {
                $output .= "**Foreign Keys:**\n\n";
                foreach ($tableInfo['foreign_keys'] as $foreignKey) {
                    if (is_string($foreignKey)) {
                        $output .= "- {$foreignKey}\n";
                    }
                }
                $output .= "\n";
            }
        }
        
        return $output;
    }

    /**
     * Find Rails models and extract information
     * 
     * @param string $projectRoot
     * @return array<int, array<string, mixed>>
     */
    private function findRailsModels(string $projectRoot): array
    {
        $models = [];
        $modelsDir = $projectRoot . '/app/models';
        
        if (!$this->filesystem->exists($modelsDir)) {
            return $models;
        }
        
        $finder = new Finder();
        $finder->files()->in($modelsDir)->name('*.rb')->notName('application_record.rb');
        
        foreach ($finder as $file) {
            $modelInfo = $this->extractModelInfo($file->getRealPath());
            if ($modelInfo) {
                $models[] = $modelInfo;
            }
        }
        
        return $models;
    }

    /**
     * Extract model information from a file
     * 
     * @param string $filePath
     * @return array<string, mixed>|null
     */
    private function extractModelInfo(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        $fileName = basename($filePath, '.rb');
        
        // Convert snake_case to CamelCase for model name
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $fileName)));
        
        $model = [
            'name' => $modelName,
            'associations' => [],
            'validations' => [],
            'scopes' => []
        ];
        
        // Extract associations
        preg_match_all('/(belongs_to|has_many|has_one|has_and_belongs_to_many)\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $assocMatches, PREG_SET_ORDER);
        foreach ($assocMatches as $match) {
            $assocType = $match[1];
            $assocName = $match[2];
            $assocOptions = isset($match[3]) ? trim($match[3]) : '';
            
            $model['associations'][] = "$assocType :$assocName" . ($assocOptions ? ", $assocOptions" : '');
        }
        
        // Extract validations
        preg_match_all('/validates(?:_[a-z_]+)?\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $validMatches, PREG_SET_ORDER);
        foreach ($validMatches as $match) {
            $validField = $match[1];
            $validOptions = isset($match[2]) ? trim($match[2]) : '';
            
            $model['validations'][] = "validates :$validField" . ($validOptions ? ", $validOptions" : '');
        }
        
        // Extract scopes
        preg_match_all('/scope\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $scopeMatches, PREG_SET_ORDER);
        foreach ($scopeMatches as $match) {
            $scopeName = $match[1];
            $scopeDefinition = isset($match[2]) ? trim($match[2]) : '';
            
            $model['scopes'][] = "scope :$scopeName" . ($scopeDefinition ? ", $scopeDefinition" : '');
        }
        
        return $model;
    }
}
