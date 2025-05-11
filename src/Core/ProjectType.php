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
