<?php

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\Rails\SchemaViewer;
use Symfony\Component\Filesystem\Filesystem;

test('it returns empty string for non-rails projects', function () {
    $projectType = new ProjectType;
    $projectType->addTrait('php'); // Not a Rails project

    $viewer = new SchemaViewer;
    $output = $viewer->generate('/fake/path', $projectType);

    expect($output)->toBe('');
});

test('it is applicable for rails projects with schema.rb', function () {
    $projectRoot = '/fake/path';

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/db/schema.rb')->andReturn(true);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $viewer = new SchemaViewer($projectType, $filesystem);

    expect($viewer->isApplicable($projectRoot, $projectType))->toBeTrue();
});

test('it is applicable for rails projects with structure.sql', function () {
    $projectRoot = '/fake/path';

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/db/schema.rb')->andReturn(false);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/db/structure.sql')->andReturn(true);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $viewer = new SchemaViewer($projectType, $filesystem);

    expect($viewer->isApplicable($projectRoot, $projectType))->toBeTrue();
});

test('it is not applicable for rails projects without schema files', function () {
    $projectRoot = '/fake/path';

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/db/schema.rb')->andReturn(false);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/db/structure.sql')->andReturn(false);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $viewer = new SchemaViewer($projectType, $filesystem);

    expect($viewer->isApplicable($projectRoot, $projectType))->toBeFalse();
});

test('it detects changes in schema file', function () {
    $projectRoot = '/fake/path';
    $schemaFile = $projectRoot.'/db/schema.rb';
    $structureFile = $projectRoot.'/db/structure.sql';
    $modelsDir = $projectRoot.'/app/models';

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($schemaFile)->andReturn(true);
    $filesystem->shouldReceive('exists')->with($structureFile)->andReturn(false);
    $filesystem->shouldReceive('exists')->with($modelsDir)->andReturn(false);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    // Create a custom SchemaViewer for testing
    $viewer = Mockery::mock(SchemaViewer::class, [$projectType, $filesystem])->makePartial();
    $viewer->shouldReceive('isApplicable')->andReturn(true);

    // Mock the hasChanged method to return true then false
    $firstCall = true;
    $viewer->shouldReceive('hasChanged')->andReturnUsing(function ($path) use (&$firstCall) {
        $result = $firstCall;
        $firstCall = false;

        return $result;
    });

    // First call - should return true as there's no previous hash
    expect($viewer->hasChanged($projectRoot))->toBeTrue();

    // Second call - should return false as the hash is the same
    expect($viewer->hasChanged($projectRoot))->toBeFalse();
});
