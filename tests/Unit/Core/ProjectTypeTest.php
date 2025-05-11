<?php

use DuaneStorey\AiTools\Core\ProjectType;

test('it can add and check for rails trait', function () {
    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    expect($projectType->hasTrait('rails'))->toBeTrue();
    expect($projectType->isRails())->toBeTrue();
});

test('it can get rails project description', function () {
    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    expect($projectType->getDescription())->toBe('Ruby on Rails Project');
});

test('it can get rails project description with version', function () {
    $projectType = new ProjectType;
    $projectType->addTrait('rails');
    $projectType->setMetadata('rails_version', '7');

    expect($projectType->getDescription())->toBe('Ruby on Rails 7.x Project');
});
