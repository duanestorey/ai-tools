## Project Information

- **Project Type**: PHP Project
- **Traits**: php
- **PHP Version**: 8.4.7
- **Project Name**: duanestorey/ai-tools
- **Description**: Tools for generating AI-friendly project overviews
- **License**: MIT

# Directory Tree

```
ai-tools
├── .github
│   └── workflows
│   │   └── update-homebrew.yml
├── bin
│   └── ai-overview
├── docs
│   └── CREATING_CUSTOM_VIEWERS.md
├── homebrew
│   ├── ai-tools.rb
│   └── README.md
├── src
│   ├── Console
│   │   ├── Command
│   │   │   ├── GenerateAllCommand.php
│   │   │   ├── GenerateOverviewCommand.php
│   │   │   └── InitConfigCommand.php
│   │   └── Application.php
│   ├── Core
│   │   ├── Configuration.php
│   │   ├── OverviewGenerator.php
│   │   ├── ProjectFinder.php
│   │   ├── ProjectType.php
│   │   ├── ProjectTypeDetector.php
│   │   └── Version.php
│   └── Viewers
│   │   ├── Laravel
│   │   │   ├── RoutesViewer.php
│   │   │   └── SchemaViewer.php
│   │   ├── Rails
│   │   │   ├── RoutesViewer.php
│   │   │   └── SchemaViewer.php
│   │   ├── AllCodeFilesViewer.php
│   │   ├── ComposerJsonViewer.php
│   │   ├── DirectoryTreeViewer.php
│   │   ├── EnvVariablesViewer.php
│   │   ├── GitInfoViewer.php
│   │   ├── PackageJsonViewer.php
│   │   ├── ProjectInfoViewer.php
│   │   ├── ReadmeViewer.php
│   │   └── ViewerInterface.php
├── .ai-tools-test.json
├── .ai-tools.json
├── .DS_Store
├── ai-project-overview.md
├── composer.json
├── composer.lock
├── debug-output.txt
├── phpstan.neon
├── pint.json
├── README.md
├── test-exclusion.php
└── test-extensions.php

```

# Composer JSON

```json
{
    "name": "duanestorey/ai-tools",
    "description": "Tools for generating AI-friendly project overviews",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Duane Storey",
            "email": "duanestorey@gmail.com"
        }
    ],
    "minimum-stability": "stable",
    "require": {
        "php": "^8.0",
        "symfony/console": "*",
        "symfony/finder": "*",
        "symfony/filesystem": "*",
        "symfony/process": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "*",
        "laravel/pint": "*",
        "phpstan/phpstan": "*",
        "pestphp/pest": "*",
        "mockery/mockery": "*"
    },
    "autoload": {
        "psr-4": {
            "DuaneStorey\\AiTools\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "DuaneStorey\\AiTools\\Tests\\": "tests/"
        }
    },
    "bin": [
        "bin/ai-overview"
    ],
    "scripts": {
        "lint": "pint",
        "analyse": "phpstan analyse",
        "test": "pest",
        "test:coverage": "pest --coverage",
        "quality": [
            "@lint",
            "@analyse",
            "@test"
        ]
    },
    "config": {
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    }
}
```

# README Content

# AI Tools

A Composer package for generating AI-friendly project overviews. This tool creates a comprehensive markdown file that provides AI assistants with a complete overview of your project structure and key files, making it easier for AI to understand your codebase context.

## Installation

### Via Composer

```bash
composer require duanestorey/ai-tools --dev
```

### Manual Installation

1. Clone this repository
2. Run `composer install` in the cloned directory
3. Link the executable to your project's vendor/bin directory

## Usage

### Basic Usage

After installation, you can generate an AI-friendly overview of your project:

```bash
# Initialize configuration file
vendor/bin/ai-overview init

# Generate overview once
vendor/bin/ai-overview generate

# Generate and watch for changes
vendor/bin/ai-overview generate --watch

# Generate overview with all code files included
vendor/bin/ai-overview generate-all
```

This will create an `ai-overview.md` file in your project root with:

1. **Project Information**: Details about the detected project type and framework
2. **Directory Tree**: A complete ASCII representation of your project structure (excluding directories and files specified in the configuration)
3. **Package JSON**: Contents of package.json (if exists in your project)
4. **Composer JSON**: Contents of your composer.json file
5. **README**: Contents of your project's README.md file (if exists)
6. **Git Information**: Repository URL, branches, and git configuration
7. **Environment Variables**: Keys from .env files (values omitted for security)

For Laravel projects, additional sections are automatically included:

8. **Laravel Routes**: API and web routes with their controllers
9. **Database Schema**: Table structure extracted from migrations and models

For comprehensive AI analysis, you can also generate a complete overview that includes the full content of all code files:

```bash
vendor/bin/ai-overview generate-all
```

This will create an `ai-overview-all.md` file that includes everything in the regular overview plus the full content of all code files in your project (respecting the exclusion settings in your configuration). This is particularly useful when you want to provide a complete view of your codebase to AI tools for deep analysis.

### Code Quality Tools

This package includes several tools to maintain code quality:

- **Laravel Pint**: A PHP code style fixer based on PHP-CS-Fixer
- **PHPStan/Larastan**: A static analysis tool to find bugs and errors

You can run these tools using the following commands:

```bash
# Format code using Laravel Pint
composer pint

# Check code style without making changes
composer pint-test

# Run static analysis with PHPStan
composer phpstan

# Run all quality checks (format, test, and analyse)
composer quality
```

### Integrating with Build Processes

#### Composer Scripts

Add the command to your `composer.json` scripts section for easy execution:

```json
"scripts": {
    "generate-ai-overview": "ai-overview generate",
    "build": [
        "@other-build-commands",
        "@generate-ai-overview"
    ]
}
```

Now you can run `composer generate-ai-overview` to generate the overview, or it will automatically run as part of your build process when you execute `composer build`.

#### CI/CD Integration

Add the overview generation to your CI/CD pipeline to ensure it's always up-to-date:

```yaml
# Example GitHub Actions workflow step
- name: Generate AI Overview
  run: vendor/bin/ai-overview generate
```

#### Git Hooks

You can use Git hooks to automatically generate the overview before commits or pushes:

```bash
# In .git/hooks/pre-commit or using husky with package.json
vendor/bin/ai-overview generate
```

## Configuration

The tool can be configured using a `.ai-tools.json` file in your project root. To create an example configuration file, run:

```bash
php bin/ai-overview init
```

This will create a `.ai-tools.json` file with default settings and add it to your `.gitignore` file if one exists.

Here's an example configuration file:

```json
{
  "output_file": "ai-overview.md",
  "excluded_directories": [".git", "vendor", "node_modules", "path/to/specific/directory"],
  "excluded_files": [".env"],
  "directory_tree": {
    "max_depth": 4
  },
  "viewers": {
    "project_info": true,
    "directory_tree": true,
    "composer_json": true,
    "package_json": true,
    "readme": true,
    "git_info": true,
    "env_variables": true,
    "laravel_routes": true,
    "laravel_schema": true
  },
  "code_files": {
    "extensions": [
      ".php", ".blade.php", ".js", ".css", 
      ".rb", ".erb", ".yml", ".json"
    ]
  }
}
```

### Configuration Options

- **output_file**: The name of the generated overview file (default: `ai-overview.md`). When using `generate-all`, it will create a file with "-all" appended before the extension (e.g., `ai-overview-all.md`)
- **excluded_directories**: Directories to exclude from the directory tree, file watching, and code file inclusion when using `generate-all`. Supports both simple directory names (e.g., `vendor`) and path-based exclusions (e.g., `path/to/directory`)
- **excluded_files**: Files to exclude from the directory tree, file watching, and code file inclusion when using `generate-all`
- **directory_tree**: Configuration options for the directory tree viewer
  - **max_depth**: Maximum depth to display in the directory tree (default: `4`)
- **viewers**: Enable or disable specific viewers
- **code_files**: Configuration options for the code files included in `generate-all` command
  - **extensions**: Array of file extensions to include when using the `generate-all` command. If not specified, default extensions for PHP/Laravel and Ruby on Rails will be used.

## How It Works

### Project Type Detection

The tool automatically detects the type of project you're working with. Currently, it can identify:

- **Laravel Projects**: Detected by the presence of artisan file, app/Http/Controllers directory, or Laravel dependencies
- **PHP Projects**: Any PHP project that isn't a recognized framework

Based on the detected project type, the tool automatically includes framework-specific information in the overview.

### Viewer Architecture

The tool uses a plugin-based architecture with "viewers" for different content types. Each viewer is responsible for generating a specific section of the overview file. The current viewers include:

#### Core Viewers (Always Included)

- **ProjectInfoViewer**: Provides information about the detected project type, PHP version, and project metadata
- **DirectoryTreeViewer**: Generates an ASCII representation of your project structure, respecting excluded directories/files and max depth settings
- **ComposerJsonViewer**: Includes the contents of your composer.json file
- **PackageJsonViewer**: Includes the contents of your package.json file (if present)
- **ReadmeViewer**: Includes the contents of your README.md file (if present)
- **GitInfoViewer**: Shows Git repository information including repository URL and branches
- **EnvVariablesViewer**: Shows environment variable keys from .env files (values omitted for security)

#### Framework-Specific Viewers

**Laravel Viewers** (Only included for Laravel projects):
- **RoutesViewer**: Shows API and web routes with their controllers
- **SchemaViewer**: Extracts database schema from migrations and models

The tool detects changes in your project and only regenerates the overview file when necessary, making it efficient even in watch mode.

## Benefits for AI Assistants

When working with AI coding assistants, the `ai-overview.md` file provides:

1. Immediate project structure understanding without requiring multiple queries
2. Context about dependencies and project configuration
3. Access to your project's documentation

This helps AI assistants provide more accurate and contextually relevant assistance from the start of your conversation.

## Extending the Tool

You can extend the tool with custom viewers by implementing the `ViewerInterface`. This allows you to include additional project information that might be helpful for AI assistants.

## License

MIT

# Git Information

## Repository URL

`https://github.com/duanestorey/ai-tools.git`

## Branches

```
* feature-generate-all
main
remotes/origin/main
```


# Environment Variables

Not applicable for this project.
# All Code Files

This section contains all code files in the project.

## File: .ai-tools-test.json

```
{
  "output_file": "ai-project-overview.md",
  "excluded_directories": [
    ".git",
    "vendor",
    "node_modules",
    "tests"
  ],
  "excluded_files": [
    ".env",
    ".gitignore"
  ],
  "directory_tree": {
    "max_depth": 4
  },
  "viewers": {
    "project_info": true,
    "directory_tree": true,
    "composer_json": true,
    "package_json": false,
    "readme": true,
    "git_info": true,
    "env_variables": true,
    "laravel_routes": true,
    "laravel_schema": true
  },
  "code_files": {
    "extensions": [
      ".php", ".json", ".md"
    ]
  }
} 
```

## File: .ai-tools.json

```
{
  "output_file": "ai-project-overview.md",
  "excluded_directories": [
    ".git",
    "vendor",
    "node_modules",
    "tests"
  ],
  "excluded_files": [
    ".env",
    ".gitignore"
  ],
  "directory_tree": {
    "max_depth": 4
  },
  "viewers": {
    "project_info": true,
    "directory_tree": true,
    "composer_json": true,
    "package_json": false,
    "readme": true,
    "git_info": true,
    "env_variables": true,
    "laravel_routes": true,
    "laravel_schema": true
  },
  "code_files": {
    "extensions": [
      ".php", ".json", ".md"
    ]
  }
} 
```

## File: README.md

```
# AI Tools

A Composer package for generating AI-friendly project overviews. This tool creates a comprehensive markdown file that provides AI assistants with a complete overview of your project structure and key files, making it easier for AI to understand your codebase context.

## Installation

### Via Composer

```bash
composer require duanestorey/ai-tools --dev
```

### Manual Installation

1. Clone this repository
2. Run `composer install` in the cloned directory
3. Link the executable to your project's vendor/bin directory

## Usage

### Basic Usage

After installation, you can generate an AI-friendly overview of your project:

```bash
# Initialize configuration file
vendor/bin/ai-overview init

# Generate overview once
vendor/bin/ai-overview generate

# Generate and watch for changes
vendor/bin/ai-overview generate --watch

# Generate overview with all code files included
vendor/bin/ai-overview generate-all
```

This will create an `ai-overview.md` file in your project root with:

1. **Project Information**: Details about the detected project type and framework
2. **Directory Tree**: A complete ASCII representation of your project structure (excluding directories and files specified in the configuration)
3. **Package JSON**: Contents of package.json (if exists in your project)
4. **Composer JSON**: Contents of your composer.json file
5. **README**: Contents of your project's README.md file (if exists)
6. **Git Information**: Repository URL, branches, and git configuration
7. **Environment Variables**: Keys from .env files (values omitted for security)

For Laravel projects, additional sections are automatically included:

8. **Laravel Routes**: API and web routes with their controllers
9. **Database Schema**: Table structure extracted from migrations and models

For comprehensive AI analysis, you can also generate a complete overview that includes the full content of all code files:

```bash
vendor/bin/ai-overview generate-all
```

This will create an `ai-overview-all.md` file that includes everything in the regular overview plus the full content of all code files in your project (respecting the exclusion settings in your configuration). This is particularly useful when you want to provide a complete view of your codebase to AI tools for deep analysis.

### Code Quality Tools

This package includes several tools to maintain code quality:

- **Laravel Pint**: A PHP code style fixer based on PHP-CS-Fixer
- **PHPStan/Larastan**: A static analysis tool to find bugs and errors

You can run these tools using the following commands:

```bash
# Format code using Laravel Pint
composer pint

# Check code style without making changes
composer pint-test

# Run static analysis with PHPStan
composer phpstan

# Run all quality checks (format, test, and analyse)
composer quality
```

### Integrating with Build Processes

#### Composer Scripts

Add the command to your `composer.json` scripts section for easy execution:

```json
"scripts": {
    "generate-ai-overview": "ai-overview generate",
    "build": [
        "@other-build-commands",
        "@generate-ai-overview"
    ]
}
```

Now you can run `composer generate-ai-overview` to generate the overview, or it will automatically run as part of your build process when you execute `composer build`.

#### CI/CD Integration

Add the overview generation to your CI/CD pipeline to ensure it's always up-to-date:

```yaml
# Example GitHub Actions workflow step
- name: Generate AI Overview
  run: vendor/bin/ai-overview generate
```

#### Git Hooks

You can use Git hooks to automatically generate the overview before commits or pushes:

```bash
# In .git/hooks/pre-commit or using husky with package.json
vendor/bin/ai-overview generate
```

## Configuration

The tool can be configured using a `.ai-tools.json` file in your project root. To create an example configuration file, run:

```bash
php bin/ai-overview init
```

This will create a `.ai-tools.json` file with default settings and add it to your `.gitignore` file if one exists.

Here's an example configuration file:

```json
{
  "output_file": "ai-overview.md",
  "excluded_directories": [".git", "vendor", "node_modules", "path/to/specific/directory"],
  "excluded_files": [".env"],
  "directory_tree": {
    "max_depth": 4
  },
  "viewers": {
    "project_info": true,
    "directory_tree": true,
    "composer_json": true,
    "package_json": true,
    "readme": true,
    "git_info": true,
    "env_variables": true,
    "laravel_routes": true,
    "laravel_schema": true
  },
  "code_files": {
    "extensions": [
      ".php", ".blade.php", ".js", ".css", 
      ".rb", ".erb", ".yml", ".json"
    ]
  }
}
```

### Configuration Options

- **output_file**: The name of the generated overview file (default: `ai-overview.md`). When using `generate-all`, it will create a file with "-all" appended before the extension (e.g., `ai-overview-all.md`)
- **excluded_directories**: Directories to exclude from the directory tree, file watching, and code file inclusion when using `generate-all`. Supports both simple directory names (e.g., `vendor`) and path-based exclusions (e.g., `path/to/directory`)
- **excluded_files**: Files to exclude from the directory tree, file watching, and code file inclusion when using `generate-all`
- **directory_tree**: Configuration options for the directory tree viewer
  - **max_depth**: Maximum depth to display in the directory tree (default: `4`)
- **viewers**: Enable or disable specific viewers
- **code_files**: Configuration options for the code files included in `generate-all` command
  - **extensions**: Array of file extensions to include when using the `generate-all` command. If not specified, default extensions for PHP/Laravel and Ruby on Rails will be used.

## How It Works

### Project Type Detection

The tool automatically detects the type of project you're working with. Currently, it can identify:

- **Laravel Projects**: Detected by the presence of artisan file, app/Http/Controllers directory, or Laravel dependencies
- **PHP Projects**: Any PHP project that isn't a recognized framework

Based on the detected project type, the tool automatically includes framework-specific information in the overview.

### Viewer Architecture

The tool uses a plugin-based architecture with "viewers" for different content types. Each viewer is responsible for generating a specific section of the overview file. The current viewers include:

#### Core Viewers (Always Included)

- **ProjectInfoViewer**: Provides information about the detected project type, PHP version, and project metadata
- **DirectoryTreeViewer**: Generates an ASCII representation of your project structure, respecting excluded directories/files and max depth settings
- **ComposerJsonViewer**: Includes the contents of your composer.json file
- **PackageJsonViewer**: Includes the contents of your package.json file (if present)
- **ReadmeViewer**: Includes the contents of your README.md file (if present)
- **GitInfoViewer**: Shows Git repository information including repository URL and branches
- **EnvVariablesViewer**: Shows environment variable keys from .env files (values omitted for security)

#### Framework-Specific Viewers

**Laravel Viewers** (Only included for Laravel projects):
- **RoutesViewer**: Shows API and web routes with their controllers
- **SchemaViewer**: Extracts database schema from migrations and models

The tool detects changes in your project and only regenerates the overview file when necessary, making it efficient even in watch mode.

## Benefits for AI Assistants

When working with AI coding assistants, the `ai-overview.md` file provides:

1. Immediate project structure understanding without requiring multiple queries
2. Context about dependencies and project configuration
3. Access to your project's documentation

This helps AI assistants provide more accurate and contextually relevant assistance from the start of your conversation.

## Extending the Tool

You can extend the tool with custom viewers by implementing the `ViewerInterface`. This allows you to include additional project information that might be helpful for AI assistants.

## License

MIT

```

## File: composer.json

```
{
    "name": "duanestorey/ai-tools",
    "description": "Tools for generating AI-friendly project overviews",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Duane Storey",
            "email": "duanestorey@gmail.com"
        }
    ],
    "minimum-stability": "stable",
    "require": {
        "php": "^8.0",
        "symfony/console": "*",
        "symfony/finder": "*",
        "symfony/filesystem": "*",
        "symfony/process": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "*",
        "laravel/pint": "*",
        "phpstan/phpstan": "*",
        "pestphp/pest": "*",
        "mockery/mockery": "*"
    },
    "autoload": {
        "psr-4": {
            "DuaneStorey\\AiTools\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "DuaneStorey\\AiTools\\Tests\\": "tests/"
        }
    },
    "bin": [
        "bin/ai-overview"
    ],
    "scripts": {
        "lint": "pint",
        "analyse": "phpstan analyse",
        "test": "pest",
        "test:coverage": "pest --coverage",
        "quality": ["@lint", "@analyse", "@test"]
    },
    "config": {
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    }
}

```

## File: docs/CREATING_CUSTOM_VIEWERS.md

```
# Creating Custom Viewers

This guide explains how to extend the `duanestorey/ai-tools` package with your own custom viewers to include additional information in the AI overview file.

## Understanding Viewers

Viewers are responsible for generating specific sections of the `ai-overview.md` file. Each viewer:

1. Determines if it's applicable to the current project
2. Generates markdown content for its section
3. Detects if relevant files have changed since the last generation

## Creating a Custom Viewer

### Step 1: Implement the ViewerInterface

Create a new PHP class that implements the `DuaneStorey\AiTools\Viewers\ViewerInterface`. This interface requires four methods:

```php
namespace DuaneStorey\AiTools\Viewers;

interface ViewerInterface
{
    /**
     * Get the name of the viewer for display purposes
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Check if this viewer is applicable for the given project
     *
     * @param string $projectRoot
     * @return bool
     */
    public function isApplicable(string $projectRoot): bool;
    
    /**
     * Generate the markdown content for this viewer
     *
     * @param string $projectRoot
     * @return string
     */
    public function generate(string $projectRoot): string;
    
    /**
     * Check if relevant files have changed since last generation
     *
     * @param string $projectRoot
     * @return bool
     */
    public function hasChanged(string $projectRoot): bool;
}
```

### Step 2: Implement Your Custom Logic

Here's an example of a simple viewer that shows the PHP version information:

```php
<?php

namespace YourNamespace\Viewers;

use DuaneStorey\AiTools\Viewers\ViewerInterface;
use Symfony\Component\Process\Process;

class PhpInfoViewer implements ViewerInterface
{
    private $lastHash = null;
    
    public function getName(): string
    {
        return 'PHP Information';
    }
    
    public function isApplicable(string $projectRoot): bool
    {
        // Always applicable if PHP is installed
        return true;
    }
    
    public function generate(string $projectRoot): string
    {
        $output = "# PHP Information\n\n";
        
        // Get PHP version
        $phpVersion = PHP_VERSION;
        $output .= "## PHP Version\n\n`{$phpVersion}`\n\n";
        
        // Get PHP extensions
        $extensions = get_loaded_extensions();
        sort($extensions);
        
        $output .= "## Loaded Extensions\n\n```\n";
        foreach ($extensions as $extension) {
            $output .= "- {$extension}\n";
        }
        $output .= "```\n\n";
        
        return $output;
    }
    
    public function hasChanged(string $projectRoot): bool
    {
        // Generate a hash of the PHP info
        $hash = md5(PHP_VERSION . implode(',', get_loaded_extensions()));
        
        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }
        
        $this->lastHash = $hash;
        return true;
    }
}
```

### Step 3: Register Your Viewer

There are two ways to register your custom viewer:

#### Option 1: Extend the OverviewGenerator Class

Create a custom extension of the `OverviewGenerator` class and override the `registerViewers` method:

```php
<?php

namespace YourNamespace;

use DuaneStorey\AiTools\Core\OverviewGenerator as BaseGenerator;
use YourNamespace\Viewers\PhpInfoViewer;

class CustomOverviewGenerator extends BaseGenerator
{
    protected function registerViewers(): void
    {
        // Call parent to register default viewers
        parent::registerViewers();
        
        // Add your custom viewer
        $this->viewers[] = new PhpInfoViewer();
    }
}
```

Then use your custom generator in your own command or script.

#### Option 2: Create a Plugin for the Package

If you want to distribute your viewer as a plugin for others to use:

1. Create a Composer package for your viewer
2. In your package's service provider or initialization code, hook into the main package's viewer registration

## Best Practices for Custom Viewers

1. **Focus on AI Relevance**: Include information that would be helpful for AI assistants to understand your project
2. **Handle Errors Gracefully**: Your viewer should never cause the entire process to fail
3. **Optimize for Performance**: Keep the `hasChanged()` method lightweight
4. **Secure Sensitive Information**: Never include API keys, passwords, or other sensitive data
5. **Format Output Properly**: Use proper markdown formatting for readability

## Example Viewer Ideas

Here are some ideas for custom viewers you might want to implement:

- **Framework-specific Viewers**: For Laravel, Symfony, or other frameworks
- **Testing Coverage Viewer**: Show code coverage statistics
- **Dependency Graph Viewer**: Visualize project dependencies
- **Code Quality Metrics Viewer**: Include results from tools like PHPStan or PHPCS
- **Documentation Viewer**: Extract and include PHPDoc comments for key classes

## Need Help?

If you need assistance creating custom viewers, please open an issue on the GitHub repository.

```

## File: homebrew/README.md

```
# Homebrew Tap for AI Tools

This repository contains Homebrew formulae for the [AI Tools](https://github.com/duanestorey/ai-tools) package.

## Repository Structure

There are two repositories involved in this project:

1. **[ai-tools](https://github.com/duanestorey/ai-tools)** - The main repository containing the PHP code (used by Composer)
2. **[homebrew-ai-tools](https://github.com/duanestorey/homebrew-ai-tools)** - The Homebrew tap repository (required by Homebrew's naming convention)

## Installation

```bash
# Add the tap
brew tap duanestorey/ai-tools

# Install AI Tools
brew install ai-tools
```

## Usage

After installation, you can use the tool with:

```bash
# Generate an AI overview once
ai-tools generate

# Generate and watch for changes
ai-tools generate --watch
```

## Updating

To update to the latest version:

```bash
brew update
brew upgrade ai-tools
```

```

## File: pint.json

```
{
    "preset": "laravel",
    "rules": {
        "array_syntax": {
            "syntax": "short"
        },
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "no_unused_imports": true,
        "not_operator_with_successor_space": true,
        "phpdoc_scalar": true,
        "phpdoc_separation": true,
        "phpdoc_order": true,
        "phpdoc_trim": true,
        "phpdoc_align": true,
        "phpdoc_types": true,
        "phpdoc_single_line_var_spacing": true
    }
}

```

## File: src/Console/Application.php

```
<?php

namespace DuaneStorey\AiTools\Console;

use DuaneStorey\AiTools\Console\Command\GenerateAllCommand;
use DuaneStorey\AiTools\Console\Command\GenerateOverviewCommand;
use DuaneStorey\AiTools\Console\Command\InitConfigCommand;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('duanestorey/ai-tools', Version::get());

        $this->add(new GenerateOverviewCommand);
        $this->add(new InitConfigCommand);
        $this->add(new GenerateAllCommand);
    }
}

```

## File: src/Console/Command/GenerateAllCommand.php

```
<?php

namespace DuaneStorey\AiTools\Console\Command;

use DuaneStorey\AiTools\Core\OverviewGenerator;
use DuaneStorey\AiTools\Core\ProjectFinder;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateAllCommand extends Command
{
    protected static $defaultName = 'generate-all';

    protected static $defaultDescription = 'Generate AI-friendly project overview with all code files included';
    
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and regenerate automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('<fg=green>duanestorey/ai-tools v%s</>', Version::get()));

        // Find project root
        $projectFinder = new ProjectFinder;
        $projectRoot = $projectFinder->findProjectRoot();

        if (! $projectRoot) {
            $io->error('Could not determine project root. Make sure you run this command from a valid project directory.');

            return Command::FAILURE;
        }

        $io->text(sprintf('Project root: <info>%s</info>', $projectRoot));

        // Create overview generator with all code files
        $generator = new OverviewGenerator($projectRoot, $io, true);

        // Watch mode
        if ($input->getOption('watch')) {
            $io->note('Watch mode enabled. Press Ctrl+C to stop.');

            return $generator->watchAndGenerate();
        }

        // One-time generation
        return $generator->generate();
    }
} 
```

## File: src/Console/Command/GenerateOverviewCommand.php

```
<?php

namespace DuaneStorey\AiTools\Console\Command;

use DuaneStorey\AiTools\Core\OverviewGenerator;
use DuaneStorey\AiTools\Core\ProjectFinder;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateOverviewCommand extends Command
{
    protected static $defaultName = 'generate';

    protected static $defaultDescription = 'Generate AI-friendly project overview';
    
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and regenerate automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('<fg=green>duanestorey/ai-tools v%s</>', Version::get()));

        // Find project root
        $projectFinder = new ProjectFinder;
        $projectRoot = $projectFinder->findProjectRoot();

        if (! $projectRoot) {
            $io->error('Could not determine project root. Make sure you run this command from a valid project directory.');

            return Command::FAILURE;
        }

        $io->text(sprintf('Project root: <info>%s</info>', $projectRoot));

        // Create overview generator
        $generator = new OverviewGenerator($projectRoot, $io);

        // Watch mode
        if ($input->getOption('watch')) {
            $io->note('Watch mode enabled. Press Ctrl+C to stop.');

            return $generator->watchAndGenerate();
        }

        // One-time generation
        return $generator->generate();
    }
}

```

## File: src/Console/Command/InitConfigCommand.php

```
<?php

namespace DuaneStorey\AiTools\Console\Command;

use DuaneStorey\AiTools\Core\Configuration;
use DuaneStorey\AiTools\Core\ProjectFinder;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitConfigCommand extends Command
{
    /**
     * The default name of the command
     *
     * @var string
     */
    protected static $defaultName = 'init';

    /**
     * The default description of the command
     *
     * @var string
     */
    protected static $defaultDescription = 'Create an example .ai-tools.json configuration file';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('<fg=green>duanestorey/ai-tools v%s</>', Version::get()));

        // Find project root
        $projectFinder = new ProjectFinder;
        $projectRoot = $projectFinder->findProjectRoot();

        if (! $projectRoot) {
            $io->error('Could not determine project root. Make sure you run this command from a valid project directory.');

            return Command::FAILURE;
        }

        $io->text(sprintf('Project root: <info>%s</info>', $projectRoot));

        // Create configuration file
        $config = new Configuration($projectRoot);
        if ($config->createExampleConfig($projectRoot)) {
            $io->success('Created example configuration file: .ai-tools.json');
            $io->text('You can customize this file to configure the AI overview generator.');
            $io->text('The file has been added to your .gitignore if it exists.');

            return Command::SUCCESS;
        } else {
            $io->warning('Configuration file already exists. Not overwriting.');

            return Command::SUCCESS;
        }
    }
}

```

## File: src/Core/Configuration.php

```
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
            'max_depth' => 5,
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
            'rails_routes' => true,
            'rails_schema' => true,
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

```

## File: src/Core/OverviewGenerator.php

```
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
    
    private bool $includeAllFiles;

    public function __construct(string $projectRoot, SymfonyStyle $io, bool $includeAllFiles = false)
    {
        $this->projectRoot = $projectRoot;
        $this->io = $io;
        $this->filesystem = new Filesystem;
        $this->includeAllFiles = $includeAllFiles;

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
                $this->viewers[] = new RailsRoutesViewer($this->projectType);
            }

            if ($this->config->isViewerEnabled('rails_schema')) {
                $this->viewers[] = new RailsSchemaViewer($this->projectType);
            }
        }
        
        // Add AllCodeFilesViewer for the generate-all command
        if ($this->includeAllFiles) {
            $this->viewers[] = new \DuaneStorey\AiTools\Viewers\AllCodeFilesViewer();
        }

        // Additional framework-specific viewers can be added here in the future
    }

    /**
     * Generate the overview file
     */
    public function generate(): int
    {
        $baseOutputFile = $this->config->get('output_file', 'ai-overview.md');
        
        // If we're including all files, modify the output filename
        if ($this->includeAllFiles) {
            $pathInfo = pathinfo($baseOutputFile);
            $outputFile = $pathInfo['filename'] . '-all.' . ($pathInfo['extension'] ?? 'md');
        } else {
            $outputFile = $baseOutputFile;
        }
        
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

```

## File: src/Core/ProjectFinder.php

```
<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class ProjectFinder
{
    /**
     * Find the project root directory
     *
     * @return string|null The project root path or null if not found
     */
    public function findProjectRoot(): ?string
    {
        $filesystem = new Filesystem;

        // Strategy 1: Try to find composer.json in current directory or parent directories
        $dir = getcwd();
        while ($dir !== '/' && $dir !== '') {
            if ($filesystem->exists($dir.'/composer.json')) {
                return $dir;
            }
            $dir = dirname($dir);
        }

        // Strategy 2: Try to use Composer's runtime API if available
        if (class_exists('\Composer\Factory')) {
            try {
                $composerFile = \Composer\Factory::getComposerFile();

                return dirname($composerFile);
            } catch (\Exception $e) {
                // Ignore exceptions from Composer
            }
        }

        // Strategy 3: Fall back to current directory
        return getcwd();
    }
}

```

## File: src/Core/ProjectType.php

```
<?php

namespace DuaneStorey\AiTools\Core;

class ProjectType
{
    /**
     * Project traits
     *
     * @var array<string, bool>
     */
    private $traits = [];

    /**
     * Project metadata
     *
     * @var array<string, mixed>
     */
    private $metadata = [];

    /**
     * Add a trait to the project type
     */
    public function addTrait(string $trait): void
    {
        $this->traits[$trait] = true;
    }

    /**
     * Check if the project has a specific trait
     */
    public function hasTrait(string $trait): bool
    {
        return isset($this->traits[$trait]) && $this->traits[$trait];
    }

    /**
     * Get all traits
     *
     * @return array<string>
     */
    public function getTraits(): array
    {
        return array_keys($this->traits);
    }

    /**
     * Set metadata
     *
     * @param mixed $value
     */
    public function setMetadata(string $key, $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Get metadata
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Get all metadata
     *
     * @return array<string, mixed>
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a human-readable description of the project type
     */
    public function getDescription(): string
    {
        if ($this->hasTrait('laravel')) {
            $version = $this->getMetadata('laravel_version');
            if ($version) {
                return "Laravel {$version}.x Project";
            }

            return 'Laravel Project';
        }

        if ($this->hasTrait('rails')) {
            $version = $this->getMetadata('rails_version');
            if ($version) {
                return "Ruby on Rails {$version}.x Project";
            }

            return 'Ruby on Rails Project';
        }

        return 'PHP Project';
    }

    /**
     * Check if the project is a Rails project
     */
    public function isRails(): bool
    {
        return $this->hasTrait('rails');
    }
}

```

## File: src/Core/ProjectTypeDetector.php

```
<?php

namespace DuaneStorey\AiTools\Core;

use Symfony\Component\Filesystem\Filesystem;

class ProjectTypeDetector
{
    private Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem;
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
     * Detect the project type
     */
    public function detect(string $projectRoot): ProjectType
    {
        $projectType = new ProjectType;

        // Always add the php trait
        $projectType->addTrait('php');

        // Check for Laravel
        if ($this->isLaravelProject($projectRoot)) {
            $projectType->addTrait('laravel');
            $laravelVersion = $this->detectLaravelVersion($projectRoot);
            if ($laravelVersion) {
                $projectType->setMetadata('laravel_version', $laravelVersion);
            }
        }

        // Check for Rails
        if ($this->isRailsProject($projectRoot)) {
            $projectType->addTrait('rails');
            $projectType->addTrait('ruby');
            $railsVersion = $this->detectRailsVersion($projectRoot);
            if ($railsVersion) {
                $projectType->setMetadata('rails_version', $railsVersion);
            }
        }

        // Add more framework detections here in the future

        return $projectType;
    }

    /**
     * Check if the project is a Laravel project
     */
    protected function isLaravelProject(string $projectRoot): bool
    {
        // Check for artisan file
        if ($this->filesystem->exists($projectRoot.'/artisan')) {
            return true;
        }

        // Check for app/Http/Controllers directory
        if ($this->filesystem->exists($projectRoot.'/app/Http/Controllers')) {
            return true;
        }

        // Check for Laravel dependency in composer.json
        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $composerJson = json_decode(file_get_contents($projectRoot.'/composer.json'), true);

            if (isset($composerJson['require']['laravel/framework']) ||
                isset($composerJson['require-dev']['laravel/framework'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect Laravel version
     */
    protected function detectLaravelVersion(string $projectRoot): ?string
    {
        // Try to get version from composer.json
        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $composerJson = json_decode(file_get_contents($projectRoot.'/composer.json'), true);

            if (isset($composerJson['require']['laravel/framework'])) {
                $versionConstraint = $composerJson['require']['laravel/framework'];
                // Extract version number from constraint (e.g., "^8.0" -> "8")
                if (preg_match('/\^?(\d+)/', $versionConstraint, $matches)) {
                    return $matches[1];
                }
            }
        }

        // Try to get version from composer.lock
        if ($this->filesystem->exists($projectRoot.'/composer.lock')) {
            $composerLock = json_decode(file_get_contents($projectRoot.'/composer.lock'), true);

            if (isset($composerLock['packages'])) {
                foreach ($composerLock['packages'] as $package) {
                    if ($package['name'] === 'laravel/framework') {
                        // Extract version number (e.g., "v8.83.27" -> "8")
                        if (preg_match('/v?(\d+)/', $package['version'], $matches)) {
                            return $matches[1];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if the project is a Rails project
     */
    protected function isRailsProject(string $projectRoot): bool
    {
        // Check for config/application.rb file (Rails specific)
        if ($this->filesystem->exists($projectRoot.'/config/application.rb')) {
            $content = $this->getFileContents($projectRoot.'/config/application.rb');
            if (strpos($content, 'Rails::Application') !== false) {
                return true;
            }
        }

        // Check for Gemfile with Rails
        if ($this->filesystem->exists($projectRoot.'/Gemfile')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile');
            if (preg_match('/gem\s+[\'\"](rails)[\'\"]/i', $content)) {
                return true;
            }
        }

        // Check for app/controllers directory (common in Rails)
        if ($this->filesystem->exists($projectRoot.'/app/controllers') &&
            $this->filesystem->exists($projectRoot.'/app/models')) {
            return true;
        }

        return false;
    }

    /**
     * Detect Rails version
     */
    protected function detectRailsVersion(string $projectRoot): ?string
    {
        // Try to get version from Gemfile
        if ($this->filesystem->exists($projectRoot.'/Gemfile')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile');

            // Look for gem 'rails', '~> X.Y.Z' or gem 'rails', 'X.Y.Z'
            if (preg_match('/gem\s+[\'\"](rails)[\'\"](\,\s*[\'\"](\~\>\s*)?(\d+))?/i', $content, $matches)) {
                return isset($matches[4]) ? $matches[4] : null; // Return major version number
            }
        }

        // Try to get version from Gemfile.lock
        if ($this->filesystem->exists($projectRoot.'/Gemfile.lock')) {
            $content = $this->getFileContents($projectRoot.'/Gemfile.lock');

            // Look for rails (X.Y.Z) in the dependencies section
            if (preg_match('/rails\s+\((\d+)/', $content, $matches)) {
                return $matches[1]; // Return major version number
            }
        }

        return null;
    }
}

```

## File: src/Core/Version.php

```
<?php

namespace DuaneStorey\AiTools\Core;

/**
 * Version information for the AI Tools package
 */
class Version
{
    /**
     * The current version of the package
     */
    public const VERSION = '1.1.0';

    /**
     * Get the current version of the package
     */
    public static function get(): string
    {
        return self::VERSION;
    }
}

```

## File: src/Viewers/AllCodeFilesViewer.php

```
<?php

namespace DuaneStorey\AiTools\Viewers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class AllCodeFilesViewer implements ViewerInterface
{
    /**
     * List of file extensions to include
     *
     * @var array<string>
     */
    private array $codeFileExtensions = [
        // PHP/Laravel
        '.php', '.blade.php',
        
        // JavaScript assets (needed for both Laravel and Rails)
        '.js', '.jsx', '.ts', '.tsx',
        
        // Styles (needed for both Laravel and Rails)
        '.css', '.scss', '.sass',
        
        // Ruby/Rails
        '.rb', '.rake', '.gemspec', '.ru', '.erb',
        '.haml', '.slim',
        
        // Config files (for both)
        '.yml', '.yaml', '.json', '.env',
        
        // Shell scripts (for both)
        '.sh', '.bash'
    ];

    /**
     * Get the name of this viewer
     */
    public function getName(): string
    {
        return 'All Code Files';
    }

    /**
     * Check if this viewer is applicable for the project
     *
     * @param string $projectRoot The path to the project root
     */
    public function isApplicable(string $projectRoot): bool
    {
        return true; // This viewer is always applicable
    }

    /**
     * Check if content has changed since the last generation
     *
     * @param string $projectRoot The path to the project root
     */
    public function hasChanged(string $projectRoot): bool
    {
        return true; // Always regenerate to ensure all code files are up to date
    }

    /**
     * Generate the content for this viewer
     *
     * @param string $projectRoot The path to the project root
     */
    public function generate(string $projectRoot): string
    {
        $output = "# All Code Files\n\n";
        $output .= "This section contains all code files in the project.\n\n";
        
        $excludedDirs = $this->getExcludedDirectories($projectRoot);
        $excludedFiles = $this->getExcludedFiles($projectRoot);
        
        // Get all files
        $files = $this->getAllCodeFiles($projectRoot, $excludedDirs, $excludedFiles);
        
        if (empty($files)) {
            return $output . "No code files found in the project.\n";
        }
        
        // Sort files by path
        ksort($files);
        
        // Add files to output
        foreach ($files as $relativePath => $content) {
            $output .= "## File: {$relativePath}\n\n";
            $output .= "```\n{$content}\n```\n\n";
        }
        
        return $output;
    }
    
    /**
     * Get a list of directories to exclude from the configuration
     *
     * @param string $projectRoot The path to the project root
     * @return array<string>
     */
    private function getExcludedDirectories(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        $defaultExcludedDirs = ['.git', 'vendor', 'node_modules'];
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['excluded_directories']) && is_array($config['excluded_directories'])) {
                return $config['excluded_directories'];
            }
        }
        
        return $defaultExcludedDirs;
    }
    
    /**
     * Get a list of files to exclude from the configuration
     *
     * @param string $projectRoot The path to the project root
     * @return array<string>
     */
    private function getExcludedFiles(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        $defaultExcludedFiles = ['.env'];
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['excluded_files']) && is_array($config['excluded_files'])) {
                return $config['excluded_files'];
            }
        }
        
        return $defaultExcludedFiles;
    }
    
    /**
     * Get all code files in the project
     *
     * @param string $projectRoot The path to the project root
     * @param array<string> $excludedDirs Directories to exclude
     * @param array<string> $excludedFiles Files to exclude
     * @return array<string, string> Array of file paths and their contents
     */
    private function getAllCodeFiles(string $projectRoot, array $excludedDirs, array $excludedFiles): array
    {
        $files = [];
        
        // Check for custom file extensions in configuration
        $codeFileExtensions = $this->getCustomFileExtensions($projectRoot);
        echo "\nUsing extensions: " . implode(', ', $codeFileExtensions) . "\n\n";
        
        // Get output file name to exclude it
        $configFile = $projectRoot . '/.ai-tools.json';
        $outputFile = 'ai-overview.md';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['output_file'])) {
                $outputFile = $config['output_file'];
            }
        }
        
        // Also exclude the "-all" version of the file
        $pathInfo = pathinfo($outputFile);
        $allOutputFile = $pathInfo['filename'] . '-all.' . ($pathInfo['extension'] ?? 'md');
        
        // Add output files to excluded files list
        $excludedFiles[] = $outputFile;
        $excludedFiles[] = $allOutputFile;
        
        echo "Excluding output files: $outputFile, $allOutputFile\n";
        
        $directory = new RecursiveDirectoryIterator(
            $projectRoot,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
        );
        
        $iterator = new RecursiveIteratorIterator($directory);
        
        // Track which files are included
        $includeCount = 0;
        $excludeCount = 0;
        $includeList = [];
        
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            // Skip directories
            if ($file->isDir()) {
                continue;
            }
            
            $relativePath = $this->getRelativePath($file->getPathname(), $projectRoot);
            
            // Skip files in excluded directories
            foreach ($excludedDirs as $excludedDir) {
                $excludedDirPath = trim($excludedDir, '/');
                if (strpos($relativePath, $excludedDirPath . '/') === 0 || $relativePath === $excludedDirPath) {
                    continue 2; // Skip to the next file
                }
            }
            
            // Skip excluded files
            foreach ($excludedFiles as $excludedFile) {
                if ($relativePath === $excludedFile || basename($relativePath) === $excludedFile) {
                    continue 2; // Skip to the next file
                }
            }
            
            // Get file extension
            $extension = strtolower('.' . $file->getExtension());
            
            // Strict extension check - extensions must match exactly
            $isValidExtension = false;
            foreach ($codeFileExtensions as $validExt) {
                if ($extension === $validExt) {
                    $isValidExtension = true;
                    break;
                }
            }
            
            if (!$isValidExtension) {
                $excludeCount++;
                continue;
            }
            
            // Add file to the list
            $files[$relativePath] = file_get_contents($file->getPathname());
            $includeCount++;
            $includeList[] = "{$relativePath} ({$extension})";
        }
        
        echo "Included $includeCount files, excluded $excludeCount files based on extension filtering.\n";
        echo "Included files: " . implode(', ', $includeList) . "\n";
        
        return $files;
    }
    
    /**
     * Get the relative path of a file
     *
     * @param string $path The absolute path
     * @param string $root The project root
     * @return string The relative path
     */
    private function getRelativePath(string $path, string $root): string
    {
        return ltrim(str_replace($root, '', $path), '/');
    }
    
    /**
     * Get custom file extensions from configuration or use defaults
     *
     * @param string $projectRoot The path to the project root
     * @return array<string> Array of file extensions
     */
    private function getCustomFileExtensions(string $projectRoot): array
    {
        $configFile = $projectRoot . '/.ai-tools.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['code_files']['extensions']) && is_array($config['code_files']['extensions'])) {
                // Found custom extensions in config, use them
                $extensions = $config['code_files']['extensions'];
                // Log this to stdout for debugging
                echo PHP_EOL . "Using custom file extensions: " . implode(', ', $extensions) . PHP_EOL;
                return $extensions;
            }
        }
        
        // Use default extensions
        echo PHP_EOL . "Using default file extensions" . PHP_EOL;
        return $this->codeFileExtensions;
    }
} 
```

## File: src/Viewers/ComposerJsonViewer.php

```
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

```

## File: src/Viewers/DirectoryTreeViewer.php

```
<?php

namespace DuaneStorey\AiTools\Viewers;

use DuaneStorey\AiTools\Core\Configuration;

class DirectoryTreeViewer implements ViewerInterface
{
    private ?string $lastHash = null;

    private Configuration $config;
    
    private string $projectRoot;

    public function __construct()
    {
        // No initialization needed
    }

    public function getName(): string
    {
        return 'Directory Tree';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Directory tree is always applicable
        return true;
    }

    public function generate(string $projectRoot): string
    {
        // Load configuration
        $this->config = new Configuration($projectRoot);

        $output = "# Directory Tree\n\n```\n";
        $output .= basename($projectRoot)."\n";
        
        // Store the project root for path calculations
        $this->projectRoot = $projectRoot;
        
        $this->buildTree($projectRoot, $output, 0, ''); // Pass empty string as initial relative path
        $output .= "\n```\n";

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        // Generate a hash of the directory structure
        $hash = $this->generateDirectoryHash($projectRoot);

        // Check if hash has changed
        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        // Update the hash
        $this->lastHash = $hash;

        return true;
    }

    /**
     * Build a directory tree recursively
     *
     * @param string $directory The current directory
     * @param string &$output   Reference to the output string
     * @param int    $depth     Current depth level
     * @param string $relativePath The relative path from the project root
     */
    private function buildTree(string $directory, string &$output, int $depth, string $relativePath = ''): void
    {
        // Get excluded directories and files from config
        $excludedDirs = $this->config->get('excluded_directories', ['.git', 'vendor', 'node_modules']);
        $excludedFiles = $this->config->get('excluded_files', []);
        
        // Check max depth
        $maxDepth = $this->config->get('directory_tree.max_depth', 4);
        
        if ($depth >= $maxDepth) {
            return;
        }

        // Get directory contents
        $items = scandir($directory);

        // Filter out . and .. and excluded items
        $filteredItems = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.'/'.$item;
            $isDir = is_dir($path);
            
            // Calculate the current relative path
            // Get the real path of the current directory
            $realPath = realpath($path);
            // Calculate the relative path from the project root
            $currentRelativePath = str_replace($this->projectRoot.'/', '', $realPath);
            
            // No debug statements needed

            // Skip excluded directories
            if ($isDir) {
                $shouldExclude = false;
                
                // No debug statements needed
                
                // Check simple directory name exclusions
                if (in_array($item, $excludedDirs)) {
                    $shouldExclude = true;
                }
                
                // Check path-based exclusions
                foreach ($excludedDirs as $excludedDir) {
                    // Only process path-based exclusions (containing a slash)
                    if (strpos($excludedDir, '/') !== false) {
                        // Check if the current path contains the exclusion path
                        if (strpos($currentRelativePath, $excludedDir) !== false) {
                            $shouldExclude = true;
                            break;
                        }
                    }
                }
                
                if ($shouldExclude) {
                    continue;
                }
            }

            // Skip excluded files
            if (! $isDir && in_array($item, $excludedFiles)) {
                continue;
            }

            $filteredItems[] = $item;
        }

        // Sort items (directories first, then files)
        usort($filteredItems, function ($a, $b) use ($directory) {
            $aIsDir = is_dir($directory.'/'.$a);
            $bIsDir = is_dir($directory.'/'.$b);

            if ($aIsDir && ! $bIsDir) {
                return -1;
            } elseif (! $aIsDir && $bIsDir) {
                return 1;
            } else {
                return strcasecmp($a, $b);
            }
        });

        // Add each item to the tree
        $count = count($filteredItems);
        foreach ($filteredItems as $index => $item) {
            $path = $directory.'/'.$item;
            $isDir = is_dir($path);
            $isLast = ($index === $count - 1);

            // Create the prefix based on depth
            $prefix = '';
            for ($i = 0; $i < $depth; $i++) {
                $prefix .= '│   ';
            }

            // Add the item to the output
            $connector = $isLast ? '└── ' : '├── ';
            $output .= $prefix.$connector.$item."\n";

            // Recursively process subdirectories
            if ($isDir) {
                $childPrefix = '';
                for ($i = 0; $i < $depth; $i++) {
                    $childPrefix .= '│   ';
                }

                if ($isLast) {
                    $childPrefix .= '    ';
                } else {
                    $childPrefix .= '│   ';
                }

                $this->buildTree($path, $output, $depth + 1, $currentRelativePath);
            }
        }
    }

    /**
     * Generate a hash of the directory structure
     *
     * @param string $projectRoot The project root directory
     *
     * @return string The hash
     */
    private function generateDirectoryHash(string $projectRoot): string
    {
        $output = '';
        $this->buildTree($projectRoot, $output, 0, '');

        return md5($output);
    }
}

```

## File: src/Viewers/EnvVariablesViewer.php

```
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

```

## File: src/Viewers/GitInfoViewer.php

```
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

        // Git configuration section removed as it doesn't provide significant value for AI assistants

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

```

## File: src/Viewers/Laravel/RoutesViewer.php

```
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

```

## File: src/Viewers/Laravel/SchemaViewer.php

```
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

```

## File: src/Viewers/PackageJsonViewer.php

```
<?php

namespace DuaneStorey\AiTools\Viewers;

use Symfony\Component\Filesystem\Filesystem;

class PackageJsonViewer implements ViewerInterface
{
    private Filesystem $filesystem;

    private ?string $lastHash = null;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function getName(): string
    {
        return 'Package JSON';
    }

    public function isApplicable(string $projectRoot): bool
    {
        return $this->filesystem->exists($projectRoot.'/package.json');
    }

    public function generate(string $projectRoot): string
    {
        $packageJsonPath = $projectRoot.'/package.json';

        if (! $this->filesystem->exists($packageJsonPath)) {
            return "# Package JSON\n\nNo package.json file found in the project root.";
        }

        $content = file_get_contents($packageJsonPath);

        // Try to parse and pretty print JSON
        $json = json_decode($content);
        if ($json !== null) {
            $content = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "# Package JSON\n\n```json\n".$content."\n```\n";
    }

    public function hasChanged(string $projectRoot): bool
    {
        $packageJsonPath = $projectRoot.'/package.json';

        if (! $this->filesystem->exists($packageJsonPath)) {
            return false;
        }

        $hash = md5_file($packageJsonPath);

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }
}

```

## File: src/Viewers/ProjectInfoViewer.php

```
<?php

namespace DuaneStorey\AiTools\Viewers;

use DuaneStorey\AiTools\Core\ProjectType;
use Symfony\Component\Filesystem\Filesystem;

class ProjectInfoViewer implements ViewerInterface
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
        return 'Project Information';
    }

    public function isApplicable(string $projectRoot): bool
    {
        // Project info is always applicable
        return true;
    }

    public function generate(string $projectRoot): string
    {
        if (! $this->projectType) {
            return "## Project Information\n\nNo project type information available.\n\n";
        }

        $output = "## Project Information\n\n";
        $output .= "- **Project Type**: {$this->projectType->getDescription()}\n";

        // Add traits if available
        $traits = $this->projectType->getTraits();
        if (! empty($traits)) {
            $output .= '- **Traits**: '.implode(', ', $traits)."\n";
        }
        
        // Customize output based on project type
        if ($this->projectType->hasTrait('laravel')) {
            // Laravel-specific information
            $laravelVersion = $this->getFrameworkVersion($projectRoot, 'laravel');
            if ($laravelVersion) {
                $output .= "- **Laravel Version**: {$laravelVersion}\n";
            }
            
            // Add PHP version for PHP-based projects
            $phpVersion = PHP_VERSION;
            $output .= "- **PHP Version**: {$phpVersion}\n";
        } elseif ($this->projectType->hasTrait('rails')) {
            // Rails-specific information
            $railsVersion = $this->projectType->getMetadata('rails_version');
            if ($railsVersion) {
                $output .= "- **Rails Version**: {$railsVersion}\n";
            }
            
            // Add Ruby version
            $rubyVersion = $this->detectRubyVersion($projectRoot);
            if ($rubyVersion) {
                $output .= "- **Ruby Version**: {$rubyVersion}\n";
            }
            
            // Check for React in package.json
            $reactVersion = $this->detectReactVersion($projectRoot);
            if ($reactVersion) {
                $output .= "- **React Version**: {$reactVersion}\n";
            }
        } else {
            // Default for other project types
            $phpVersion = PHP_VERSION;
            $output .= "- **PHP Version**: {$phpVersion}\n";
        }
        
        // Check for Node.js in all project types
        $nodeVersion = $this->detectNodeVersion($projectRoot);
        if ($nodeVersion) {
            $output .= "- **Node.js Version**: {$nodeVersion}\n";
        }
        
        // Check for React in all projects if not already shown for Rails
        if (!$this->projectType->hasTrait('rails')) {
            $reactVersion = $this->detectReactVersion($projectRoot);
            if ($reactVersion) {
                $output .= "- **React Version**: {$reactVersion}\n";
            }
        }

        // Add additional project metadata
        $composerJsonPath = $projectRoot.'/composer.json';
        if (file_exists($composerJsonPath)) {
            $composerData = json_decode(file_get_contents($composerJsonPath), true);
            if (isset($composerData['name'])) {
                $output .= "- **Project Name**: {$composerData['name']}\n";
            }
            if (isset($composerData['description'])) {
                $output .= "- **Description**: {$composerData['description']}\n";
            }
            if (isset($composerData['license'])) {
                $output .= "- **License**: {$composerData['license']}\n";
            }
        }

        // Add metadata from project type if available
        $metadata = $this->projectType->getAllMetadata();
        if (! empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $formattedKey = str_replace('_', ' ', $key);
                $formattedKey = ucwords($formattedKey);
                $output .= "- **{$formattedKey}**: {$value}\n";
            }
        }

        return $output;
    }

    public function hasChanged(string $projectRoot): bool
    {
        // Calculate a hash based on composer.json and package.json
        $hash = '';

        if ($this->filesystem->exists($projectRoot.'/composer.json')) {
            $hash .= md5_file($projectRoot.'/composer.json');
        }

        if ($this->filesystem->exists($projectRoot.'/package.json')) {
            $hash .= md5_file($projectRoot.'/package.json');
        }

        if ($this->lastHash !== null && $this->lastHash === $hash) {
            return false;
        }

        $this->lastHash = $hash;

        return true;
    }

    /**
     * Get the version of a framework from composer.json
     *
     * @param string $projectRoot
     * @param string $framework
     *
     * @return string|null The framework version or null if not found
     */
    private function getFrameworkVersion(string $projectRoot, string $framework): ?string
    {
        // First try to get the exact version from composer.lock
        $lockPath = $projectRoot.'/composer.lock';
        if (file_exists($lockPath)) {
            $lockData = json_decode(file_get_contents($lockPath), true);
            if (isset($lockData['packages'])) {
                foreach ($lockData['packages'] as $package) {
                    if (isset($package['name']) && $package['name'] === "$framework/framework") {
                        return $package['version'] ?? null;
                    }
                }
            }
        }

        // If not found in lock file, check the required version in composer.json
        $composerPath = $projectRoot.'/composer.json';
        if (file_exists($composerPath)) {
            $composerData = json_decode(file_get_contents($composerPath), true);
            if (isset($composerData['require']["$framework/framework"])) {
                return $composerData['require']["$framework/framework"];
            }
        }

        return null;
    }

    /**
     * Detect Ruby version from .ruby-version file or Gemfile
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectRubyVersion(string $projectRoot): ?string
    {
        // Check .ruby-version file first
        $rubyVersionFile = $projectRoot.'/.ruby-version';
        if (file_exists($rubyVersionFile)) {
            $version = trim(file_get_contents($rubyVersionFile));
            if ($version) {
                return $version;
            }
        }
        
        // Check Gemfile for ruby version
        $gemfilePath = $projectRoot.'/Gemfile';
        if (file_exists($gemfilePath)) {
            $gemfileContent = file_get_contents($gemfilePath);
            if (preg_match('/ruby\s+[\"](\d+\.\d+\.\d+)[\"]/i', $gemfileContent, $matches)) {
                return $matches[1];
            }
            if (preg_match('/ruby\s+[\"](\d+\.\d+)[\"]/i', $gemfileContent, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Detect React version from package.json
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectReactVersion(string $projectRoot): ?string
    {
        $packageJsonPath = $projectRoot.'/package.json';
        if (file_exists($packageJsonPath)) {
            $packageData = json_decode(file_get_contents($packageJsonPath), true);
            
            // Check for React in dependencies
            if (isset($packageData['dependencies']['react'])) {
                return $this->cleanVersionString($packageData['dependencies']['react']);
            }
            
            // Check for React in devDependencies
            if (isset($packageData['devDependencies']['react'])) {
                return $this->cleanVersionString($packageData['devDependencies']['react']);
            }
            
            // Check for React-related dependencies that indicate React is being used
            $reactRelatedPackages = [
                'react-dom', 'react-router', 'react-router-dom', 'next', 'gatsby',
                '@remix-run/react', '@vitejs/plugin-react', 'vite-plugin-react'
            ];
            
            foreach ($reactRelatedPackages as $package) {
                if (isset($packageData['dependencies'][$package]) || isset($packageData['devDependencies'][$package])) {
                    // If we found a React-related package but not React itself, it's likely React is being used
                    // but we don't know the exact version
                    return 'Used (version unknown)';
                }
            }
            
            // Check for React scripts
            if (isset($packageData['scripts'])) {
                foreach ($packageData['scripts'] as $script => $command) {
                    if (strpos($command, 'react-scripts') !== false) {
                        return 'Used (version unknown)';
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Detect Node.js version from .nvmrc or package.json
     *
     * @param string $projectRoot
     * @return string|null
     */
    private function detectNodeVersion(string $projectRoot): ?string
    {
        // Check .nvmrc file first
        $nvmrcPath = $projectRoot.'/.nvmrc';
        if (file_exists($nvmrcPath)) {
            $version = trim(file_get_contents($nvmrcPath));
            if ($version) {
                return $version;
            }
        }
        
        // Check package.json engines
        $packageJsonPath = $projectRoot.'/package.json';
        if (file_exists($packageJsonPath)) {
            $packageData = json_decode(file_get_contents($packageJsonPath), true);
            
            // Try to get version from engines.node
            if (isset($packageData['engines']['node'])) {
                return $this->cleanVersionString($packageData['engines']['node']);
            }
            
            // If package.json exists but no specific version is found,
            // check if we can determine the Node.js version from the environment
            if ($this->isNodeProject($packageJsonPath)) {
                // Try to get the Node.js version from the system
                $nodeVersionProcess = new \Symfony\Component\Process\Process(['node', '-v']);
                $nodeVersionProcess->run();
                
                if ($nodeVersionProcess->isSuccessful()) {
                    $nodeVersion = trim($nodeVersionProcess->getOutput());
                    // Remove 'v' prefix if present
                    return ltrim($nodeVersion, 'v');
                }
                
                // If we can't get the actual version, at least indicate Node.js is used
                return 'Used (version unknown)';
            }
        }
        
        return null;
    }
    
    /**
     * Determine if this is a Node.js project based on package.json
     *
     * @param string $packageJsonPath
     * @return bool
     */
    private function isNodeProject(string $packageJsonPath): bool
    {
        if (!file_exists($packageJsonPath)) {
            return false;
        }
        
        $packageData = json_decode(file_get_contents($packageJsonPath), true);
        
        // Check if any of these common Node.js indicators are present
        $nodeIndicators = [
            'dependencies', 'devDependencies', 'scripts', 'engines',
            'main', 'type', 'bin', 'module'
        ];
        
        foreach ($nodeIndicators as $indicator) {
            if (isset($packageData[$indicator]) && !empty($packageData[$indicator])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Clean version string by removing ^, ~, etc.
     *
     * @param string $version
     * @return string
     */
    private function cleanVersionString(string $version): string
    {
        return preg_replace('/[^\d\.]/i', '', $version);
    }
}

```

## File: src/Viewers/Rails/RoutesViewer.php

```
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

```

## File: src/Viewers/Rails/SchemaViewer.php

```
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
     * Create a new finder
     */
    public function createFinder(): Finder
    {
        return new Finder;
    }

    public function getName(): string
    {
        return 'Rails Database Schema';
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

        // Check if schema.rb or structure.sql exists
        return $this->filesystem->exists($projectRoot.'/db/schema.rb') ||
               $this->filesystem->exists($projectRoot.'/db/structure.sql');
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

        $output = "## Rails Database Schema\n\n";

        // Check for schema.rb first
        $schemaFile = $projectRoot.'/db/schema.rb';
        if ($this->filesystem->exists($schemaFile)) {
            $tables = $this->extractTablesFromSchema($schemaFile);
            $output .= $this->formatTablesOutput($tables);
        } else {
            // Try structure.sql if schema.rb doesn't exist
            $structureFile = $projectRoot.'/db/structure.sql';
            if ($this->filesystem->exists($structureFile)) {
                $tables = $this->extractTablesFromStructure($structureFile);
                $output .= $this->formatTablesOutput($tables);
            }
        }

        // Extract model information
        $models = $this->findRailsModels($projectRoot);
        if (! empty($models)) {
            $output .= "\n## Model Relationships\n\n";

            foreach ($models as $model) {
                $output .= '### '.$model['name']."\n\n";

                if (! empty($model['associations'])) {
                    $output .= "**Associations:**\n\n";
                    foreach ($model['associations'] as $association) {
                        $output .= '- '.$association."\n";
                    }
                    $output .= "\n";
                }

                if (! empty($model['validations'])) {
                    $output .= "**Validations:**\n\n";
                    foreach ($model['validations'] as $validation) {
                        $output .= '- '.$validation."\n";
                    }
                    $output .= "\n";
                }

                if (! empty($model['scopes'])) {
                    $output .= "**Scopes:**\n\n";
                    foreach ($model['scopes'] as $scope) {
                        $output .= '- '.$scope."\n";
                    }
                    $output .= "\n";
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

        $hash = '';

        // Check schema.rb
        $schemaFile = $projectRoot.'/db/schema.rb';
        if ($this->filesystem->exists($schemaFile)) {
            $hash .= md5_file($schemaFile);
        }

        // Check structure.sql
        $structureFile = $projectRoot.'/db/structure.sql';
        if ($this->filesystem->exists($structureFile)) {
            $hash .= md5_file($structureFile);
        }

        // Check models
        $modelsDir = $projectRoot.'/app/models';
        if ($this->filesystem->exists($modelsDir)) {
            $finder = new Finder;
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
                'foreign_keys' => [],
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
                    'attributes' => $columnOptions,
                ];
            }

            // Extract indexes
            preg_match_all('/add_index\s+"'.preg_quote($tableName, '/').'",\s+\[([^\]]+)\](?:,\s*([^,]+))?/', $content, $indexMatches, PREG_SET_ORDER);
            foreach ($indexMatches as $indexMatch) {
                $indexColumns = $indexMatch[1];
                $indexOptions = $indexMatch[2] ?? '';
                $tables[$tableName]['indexes'][] = "Index on ($indexColumns) $indexOptions";
            }

            // Extract foreign keys
            preg_match_all('/add_foreign_key\s+"'.preg_quote($tableName, '/').'",\s+"([^"]+)"(?:,\s*([^,]+))?/', $content, $fkMatches, PREG_SET_ORDER);
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
                'foreign_keys' => [],
            ];

            // Extract columns
            preg_match_all('/\s*`?([^`\s]+)`?\s+([^\s,]+)([^,]*),?/s', $tableContent, $columnMatches, PREG_SET_ORDER);
            foreach ($columnMatches as $columnMatch) {
                // Extract values from the match array safely
                $columnName = $columnMatch[1] ?? '';
                $columnType = $columnMatch[2] ?? '';
                $columnOptions = $columnMatch[3] ?? '';

                // Skip if this is actually an index or constraint
                if (preg_match('/^(PRIMARY|KEY|CONSTRAINT|INDEX|UNIQUE)/i', $columnName)) {
                    continue;
                }

                $tables[$tableName]['columns'][] = [
                    'name' => $columnName,
                    'type' => $columnType,
                    'attributes' => trim($columnOptions),
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
            $options = '';
            if (isset($fkMatch[6])) {
                $options = $fkMatch[6];
            }

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
     */
    private function formatTablesOutput(array $tables): string
    {
        $output = "## Database Tables\n\n";

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

        return $output;
    }

    /**
     * Find Rails models and extract information
     *
     * @return array<int, array<string, mixed>>
     */
    private function findRailsModels(string $projectRoot): array
    {
        $models = [];
        $modelsDir = $projectRoot.'/app/models';

        if (! $this->filesystem->exists($modelsDir)) {
            return $models;
        }

        $finder = new Finder;
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
     * @return array<string, mixed>
     */
    private function extractModelInfo(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $fileName = basename($filePath, '.rb');

        // Convert snake_case to CamelCase for model name
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $fileName)));

        $model = [
            'name' => $modelName,
            'associations' => [],
            'validations' => [],
            'scopes' => [],
        ];

        // Extract associations
        preg_match_all('/(belongs_to|has_many|has_one|has_and_belongs_to_many)\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $assocMatches, PREG_SET_ORDER);
        foreach ($assocMatches as $match) {
            $assocType = $match[1];
            $assocName = $match[2];
            $assocOptions = isset($match[3]) ? trim($match[3]) : '';

            $model['associations'][] = "$assocType :$assocName".($assocOptions ? ", $assocOptions" : '');
        }

        // Extract validations
        preg_match_all('/validates(?:_[a-z_]+)?\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $validMatches, PREG_SET_ORDER);
        foreach ($validMatches as $match) {
            $validField = $match[1];
            $validOptions = isset($match[2]) ? trim($match[2]) : '';

            $model['validations'][] = "validates :$validField".($validOptions ? ", $validOptions" : '');
        }

        // Extract scopes
        preg_match_all('/scope\s+:([\w_]+)(?:,\s*([^#\n]+))?/', $content, $scopeMatches, PREG_SET_ORDER);
        foreach ($scopeMatches as $match) {
            $scopeName = $match[1];
            $scopeDefinition = isset($match[2]) ? trim($match[2]) : '';

            $model['scopes'][] = "scope :$scopeName".($scopeDefinition ? ", $scopeDefinition" : '');
        }

        return $model;
    }
}

```

## File: src/Viewers/ReadmeViewer.php

```
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

```

## File: src/Viewers/ViewerInterface.php

```
<?php

namespace DuaneStorey\AiTools\Viewers;

interface ViewerInterface
{
    /**
     * Get the name of the viewer for display purposes
     */
    public function getName(): string;

    /**
     * Check if this viewer is applicable for the given project
     */
    public function isApplicable(string $projectRoot): bool;

    /**
     * Generate the markdown content for this viewer
     */
    public function generate(string $projectRoot): string;

    /**
     * Check if relevant files have changed since last generation
     */
    public function hasChanged(string $projectRoot): bool;
}

```

## File: test-exclusion.php

```
<?php

require __DIR__.'/vendor/autoload.php';

use DuaneStorey\AiTools\Core\Configuration;

// Get the project root
$projectRoot = __DIR__;

// Load the configuration
$config = new Configuration($projectRoot);

// Get the excluded directories
$excludedDirs = $config->get('excluded_directories', []);

echo "Excluded directories from config:\n";
foreach ($excludedDirs as $dir) {
    echo "- {$dir}\n";
}

// Test a specific path
$testPath = 'src/Viewers/Laravel';
echo "\nTesting if '{$testPath}' is excluded:\n";

// Check if the path is in the excluded directories list
if (in_array($testPath, $excludedDirs)) {
    echo "YES - '{$testPath}' is in the excluded directories list\n";
} else {
    echo "NO - '{$testPath}' is NOT in the excluded directories list\n";
}

// Test a simple directory name
$testDir = 'Laravel';
echo "\nTesting if '{$testDir}' is excluded:\n";

// Check if the directory name is in the excluded directories list
if (in_array($testDir, $excludedDirs)) {
    echo "YES - '{$testDir}' is in the excluded directories list\n";
} else {
    echo "NO - '{$testDir}' is NOT in the excluded directories list\n";
}

```

## File: test-extensions.php

```
<?php

// Test script for file extension filtering
$extensions = ['.php', '.json', '.md'];
$testExtensions = ['.php', '.json', '.md', '.rb', '.yml', '.yaml'];

echo "Testing extension matching with in_array:\n";

foreach ($testExtensions as $ext) {
    $result = in_array($ext, $extensions, true) ? "MATCH" : "NO MATCH";
    echo "$ext - $result\n";
}

// Test with some actual file names
$files = [
    'test.php',
    'test.json',
    'test.md',
    'test.rb',
    'test.yml',
    'README.md',
    'composer.json',
    'ai-overview.rb'
];

echo "\nTesting with file names:\n";

foreach ($files as $file) {
    $extension = strtolower('.' . pathinfo($file, PATHINFO_EXTENSION));
    $result = in_array($extension, $extensions, true) ? "MATCH" : "NO MATCH";
    echo "$file (extension: $extension) - $result\n";
} 
```


