<?php

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\Rails\RoutesViewer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('it can parse root route from routes.rb file', function () {
    // Test the parseRoutesFile method directly
    $routesContent = file_get_contents(__DIR__.'/../../../Fixtures/rails/config/routes.rb');

    $viewer = new RoutesViewer;

    // Use reflection to access the private parseRoutesFile method
    $reflectionMethod = new ReflectionMethod(RoutesViewer::class, 'parseRoutesFile');
    $reflectionMethod->setAccessible(true);

    $routes = $reflectionMethod->invoke($viewer, $routesContent);

    // Find the root route
    $rootRoute = null;
    foreach ($routes as $route) {
        if ($route['path'] === '/' && $route['action'] === 'home#index') {
            $rootRoute = $route;
            break;
        }
    }

    expect($rootRoute)->not->toBeNull();
    expect($rootRoute['verb'])->toBe('GET');
    expect($rootRoute['path'])->toBe('/');
    expect($rootRoute['action'])->toBe('home#index');
    expect($rootRoute['name'])->toBe('root');
});

test('it returns empty string for non-rails projects', function () {
    $projectRoot = '/fake/path';

    $projectType = new ProjectType;
    // Not adding the 'rails' trait

    $viewer = new RoutesViewer($projectType);

    $output = $viewer->generate($projectRoot, $projectType);

    expect($output)->toBe('');
});

test('it can parse routes from rails routes command output', function () {
    $projectRoot = '/fake/path';

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/config/routes.rb')->andReturn(true);

    // Sample output from 'bundle exec rails routes'
    $routesOutput = '
      Prefix Verb   URI Pattern                  Controller#Action
        root GET    /                            home#index
       users GET    /users(.:format)             users#index
             POST   /users(.:format)             users#create
    new_user GET    /users/new(.:format)         users#new
   edit_user GET    /users/:id/edit(.:format)    users#edit
        user GET    /users/:id(.:format)         users#show
             PATCH  /users/:id(.:format)         users#update
             PUT    /users/:id(.:format)         users#update
             DELETE /users/:id(.:format)         users#destroy
    ';

    $process = Mockery::mock(Process::class);
    $process->shouldReceive('run')->andReturn(0);
    $process->shouldReceive('isSuccessful')->andReturn(true);
    $process->shouldReceive('getOutput')->andReturn($routesOutput);

    $viewer = Mockery::mock(RoutesViewer::class)->makePartial();
    $viewer->shouldReceive('getFilesystem')->andReturn($filesystem);
    $viewer->shouldReceive('createProcess')->andReturn($process);
    $viewer->shouldReceive('isApplicable')->andReturn(true);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $output = $viewer->generate($projectRoot, $projectType);

    expect($output)->toContain('## Rails Routes');
    expect($output)->toContain('GET    /                            home#index');
    expect($output)->toContain('GET    /users(.:format)             users#index');
    expect($output)->toContain('DELETE /users/:id(.:format)         users#destroy');
});
