## Project Information

- **Project Type**: PHP Project
- **Traits**: php
- **PHP Version**: 8.4.6
- **Project Name**: duanestorey/ai-tools
- **Description**: Tools for generating AI-friendly project overviews
- **License**: MIT



# Directory Tree

```
ai-tools
├── bin
│   └── ai-overview
├── docs
│   └── CREATING_CUSTOM_VIEWERS.md
├── src
│   ├── Console
│   │   ├── Command
│   │   │   └── GenerateOverviewCommand.php
│   │   └── Application.php
│   ├── Core
│   │   ├── Configuration.php
│   │   ├── OverviewGenerator.php
│   │   ├── ProjectFinder.php
│   │   ├── ProjectType.php
│   │   └── ProjectTypeDetector.php
│   └── Viewers
│   │   ├── Laravel
│   │   │   ├── RoutesViewer.php
│   │   │   └── SchemaViewer.php
│   │   ├── ComposerJsonViewer.php
│   │   ├── DirectoryTreeViewer.php
│   │   ├── EnvVariablesViewer.php
│   │   ├── GitInfoViewer.php
│   │   ├── PackageJsonViewer.php
│   │   ├── ProjectInfoViewer.php
│   │   ├── ReadmeViewer.php
│   │   └── ViewerInterface.php
├── .ai-tools.json
├── ai-overview.md
├── ai-project-overview.md
├── composer.json
├── composer.lock
└── README.md

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
            "email": "duane@example.com"
        }
    ],
    "minimum-stability": "stable",
    "require": {
        "php": "^8.0",
        "symfony/console": "^6.0",
        "symfony/finder": "^6.0",
        "symfony/filesystem": "^6.0",
        "symfony/process": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5"
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
        "test": "phpunit"
    }
}
```


# README Content

# AI Tools

A Composer package for generating AI-friendly project overviews. This tool creates a comprehensive markdown file that provides AI assistants with a complete overview of your project structure and key files, making it easier for AI to understand your codebase context.

## Installation

### Via Composer

Add the package to your project using Composer:

```bash
composer require duanestorey/ai-tools --dev
```

We recommend installing it as a dev dependency since it's primarily a development tool.

### Manual Installation

If you prefer manual installation:

1. Clone this repository
2. Run `composer install` in the cloned directory
3. Link the executable to your project's vendor/bin directory

## Usage

### Basic Usage

After installation, you can generate an AI-friendly overview of your project:

```bash
# Generate overview once
vendor/bin/ai-overview generate

# Generate and watch for changes
vendor/bin/ai-overview generate --watch
```

This will create an `ai-overview.md` file in your project root with:

1. **Project Information**: Details about the detected project type and framework
2. **Directory Tree**: A complete ASCII representation of your project structure (excluding files in .gitignore)
3. **Package JSON**: Contents of package.json (if exists in your project)
4. **Composer JSON**: Contents of your composer.json file
5. **README**: Contents of your project's README.md file (if exists)
6. **Git Information**: Repository URL, branches, and git configuration
7. **Environment Variables**: Keys from .env files (values omitted for security)

For Laravel projects, additional sections are automatically included:

8. **Laravel Routes**: API and web routes with their controllers
9. **Database Schema**: Table structure extracted from migrations and models

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
php bin/ai-overview generate --init-config
```

This will create a `.ai-tools.json` file with default settings and add it to your `.gitignore` file if one exists.

Here's an example configuration file:

```json
{
  "output_file": "ai-overview.md",
  "excluded_directories": [".git", "vendor", "node_modules"],
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
  }
}
```

### Configuration Options

- **output_file**: The name of the generated overview file (default: `ai-overview.md`)
- **excluded_directories**: Directories to exclude from the directory tree and file watching
- **excluded_files**: Files to exclude from the directory tree and file watching
- **directory_tree**: Configuration options for the directory tree viewer
  - **max_depth**: Maximum depth to display in the directory tree (default: `4`)
- **viewers**: Enable or disable specific viewers

## How It Works

### Project Type Detection

The tool automatically detects the type of project you're working with. Currently, it can identify:

- **Laravel Projects**: Detected by the presence of artisan file, app/Http/Controllers directory, or Laravel dependencies
- **PHP Projects**: Any PHP project that isn't a recognized framework

Based on the detected project type, the tool automatically includes framework-specific information in the overview.

### Viewer Architecture

The tool uses a plugin-based architecture with "viewers" for different content types. Each viewer is responsible for generating a specific section of the overview file. The current viewers include:

#### Core Viewers (Always Included)

- **ProjectInfoViewer**: Provides information about the detected project type
- **DirectoryTreeViewer**: Generates an ASCII representation of your project structure
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

## Git Configuration

```
core.repositoryformatversion=0
core.filemode=true
core.bare=false
core.logallrefupdates=true
core.ignorecase=true
core.precomposeunicode=true
submodule.active=.
remote.origin.url=https://duanestorey@github.com/duanestorey/ai-tools.git
remote.origin.fetch=+refs/heads/*:refs/remotes/origin/*
remote.origin.gtserviceaccountidentifier=fcba5cac1386d249702bd363a5d2247fde30c8424a8a5b1655cbcece72cec25c
branch.main.remote=origin
branch.main.merge=refs/heads/main
```



# Environment Variables

Not applicable for this project.

