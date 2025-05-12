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
  }
}
```

### Configuration Options

- **output_file**: The name of the generated overview file (default: `ai-overview.md`)
- **excluded_directories**: Directories to exclude from the directory tree and file watching. Supports both simple directory names (e.g., `vendor`) and path-based exclusions (e.g., `path/to/directory`)
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
