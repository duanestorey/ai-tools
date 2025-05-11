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
