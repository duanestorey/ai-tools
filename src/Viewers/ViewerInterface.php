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
