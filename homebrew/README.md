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
