<?php

use DuaneStorey\AiTools\Core\ProjectType;
use DuaneStorey\AiTools\Viewers\Rails\GemsViewer;
use Mockery;
use Symfony\Component\Filesystem\Filesystem;

test('it can generate gems output from Gemfile', function () {
    $projectRoot = '/fake/path';
    $gemfileContent = file_get_contents(__DIR__.'/../../../../Fixtures/rails/Gemfile');

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/Gemfile')->andReturn(true);

    $viewer = Mockery::mock(GemsViewer::class)->makePartial();
    $viewer->shouldReceive('getFilesystem')->andReturn($filesystem);
    $viewer->shouldReceive('getFileContents')->with($projectRoot.'/Gemfile')
        ->andReturn($gemfileContent);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $output = $viewer->generate($projectRoot, $projectType);

    expect($output)->toContain('## Rails Gems');
    expect($output)->toContain('- rails (~> 7.0.0)');
    expect($output)->toContain('- pg (~> 1.1)');
    expect($output)->toContain('- puma (~> 5.0)');
    expect($output)->toContain('- bcrypt (~> 3.1.7)');
    expect($output)->toContain('### Development & Test Gems');
    expect($output)->toContain('- byebug');
    expect($output)->toContain('- rspec-rails');
});

test('it returns empty string for non-rails projects', function () {
    $projectType = new ProjectType;
    $projectType->addTrait('php'); // Not a Rails project

    $viewer = new GemsViewer;
    $output = $viewer->generate('/fake/path', $projectType);

    expect($output)->toBe('');
});

test('it can parse gems with different formats', function () {
    $projectRoot = '/fake/path';

    // Create Gemfile content with various gem formats
    $gemfileContent = <<<'GEMFILE'
source 'https://rubygems.org'

ruby '3.0.0'

# Core gems
gem 'rails', '~> 7.0.0'
gem 'pg', '~> 1.1'
gem 'puma', '~> 5.0'
gem 'devise'
gem 'pundit', '>= 2.0'
gem 'sidekiq', '6.5.7'

# Frontend gems
gem 'turbo-rails'
gem 'stimulus-rails'
gem 'tailwindcss-rails'

# Use GitHub version
gem 'awesome_gem', github: 'author/awesome_gem'

# Use custom git source
gem 'custom_gem', git: 'https://github.com/author/custom_gem.git', branch: 'main'

# Use local path
gem 'local_gem', path: '../local_gem'

group :development, :test do
  gem 'rspec-rails'
  gem 'factory_bot_rails'
  gem 'faker'
end

group :development do
  gem 'web-console'
  gem 'spring'
end

group :test do
  gem 'capybara'
  gem 'selenium-webdriver'
end
GEMFILE;

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('exists')->with($projectRoot.'/Gemfile')->andReturn(true);

    $viewer = Mockery::mock(GemsViewer::class)->makePartial();
    $viewer->shouldReceive('getFilesystem')->andReturn($filesystem);
    $viewer->shouldReceive('getFileContents')->with($projectRoot.'/Gemfile')
        ->andReturn($gemfileContent);

    $projectType = new ProjectType;
    $projectType->addTrait('rails');

    $output = $viewer->generate($projectRoot, $projectType);

    expect($output)->toContain('## Rails Gems');
    expect($output)->toContain('- rails (~> 7.0.0)');
    expect($output)->toContain('- pg (~> 1.1)');
    expect($output)->toContain('- devise');
    expect($output)->toContain('- pundit (>= 2.0)');
    expect($output)->toContain('- sidekiq (6.5.7)');
    expect($output)->toContain('- awesome_gem (github: author/awesome_gem)');
    expect($output)->toContain('- custom_gem (git: https://github.com/author/custom_gem.git, branch: main)');
    expect($output)->toContain('### Development & Test Gems');
    expect($output)->toContain('- rspec-rails');
    expect($output)->toContain('- factory_bot_rails');
    expect($output)->toContain('### Development Gems');
    expect($output)->toContain('- web-console');
    expect($output)->toContain('- spring');
    expect($output)->toContain('### Test Gems');
    expect($output)->toContain('- capybara');
    expect($output)->toContain('- selenium-webdriver');
});
