<?php

use DuaneStorey\AiTools\Core\ProjectTypeDetector;
use Symfony\Component\Filesystem\Filesystem;

beforeEach(function () {
    $this->filesystem = Mockery::mock(Filesystem::class);
});

test('it can detect a Rails project with Gemfile', function () {
    // Setup filesystem mock to return false for all exists calls except for Gemfile
    $this->filesystem->shouldReceive('exists')->andReturn(false);
    $this->filesystem->shouldReceive('exists')->with('/fake/path/Gemfile')->andReturn(true);

    // Create a detector with our mocked filesystem
    $detector = new class($this->filesystem) extends ProjectTypeDetector
    {
        private Filesystem $mockedFilesystem;

        public function __construct(Filesystem $filesystem)
        {
            $this->mockedFilesystem = $filesystem;
            parent::__construct($filesystem);
        }

        public function getFileContents(string $path): string
        {
            if (strpos($path, 'Gemfile') !== false) {
                return "source 'https://rubygems.org'\ngem 'rails', '~> 7.0.0'";
            }

            return '';
        }

        protected function isRailsProject(string $projectRoot): bool
        {
            return true;
        }
    };

    $projectType = $detector->detect('/fake/path');

    expect($projectType->hasTrait('rails'))->toBeTrue();
    expect($projectType->hasTrait('ruby'))->toBeTrue();
});

test('it can detect a Rails project with application.rb', function () {
    // Setup filesystem mock to return false for all exists calls except for application.rb
    $this->filesystem->shouldReceive('exists')->andReturn(false);
    $this->filesystem->shouldReceive('exists')->with('/fake/path/config/application.rb')->andReturn(true);

    // Create a detector with our mocked filesystem
    $detector = new class($this->filesystem) extends ProjectTypeDetector
    {
        private Filesystem $mockedFilesystem;

        public function __construct(Filesystem $filesystem)
        {
            $this->mockedFilesystem = $filesystem;
            parent::__construct($filesystem);
        }

        public function getFileContents(string $path): string
        {
            if (strpos($path, 'application.rb') !== false) {
                return "module TestApp\n  class Application < Rails::Application\n  end\nend";
            }

            return '';
        }

        protected function isRailsProject(string $projectRoot): bool
        {
            return true;
        }
    };

    $projectType = $detector->detect('/fake/path');

    expect($projectType->hasTrait('rails'))->toBeTrue();
    expect($projectType->hasTrait('ruby'))->toBeTrue();
});

test('it can detect Rails version from Gemfile', function () {
    // Setup filesystem mock to return false for all exists calls except for Gemfile
    $this->filesystem->shouldReceive('exists')->andReturn(false);
    $this->filesystem->shouldReceive('exists')->with('/fake/path/Gemfile')->andReturn(true);

    // Create a detector with our mocked filesystem
    $detector = new class($this->filesystem) extends ProjectTypeDetector
    {
        private Filesystem $mockedFilesystem;

        public function __construct(Filesystem $filesystem)
        {
            $this->mockedFilesystem = $filesystem;
            parent::__construct($filesystem);
        }

        public function getFileContents(string $path): string
        {
            if (strpos($path, 'Gemfile') !== false) {
                return "source 'https://rubygems.org'\ngem 'rails', '~> 7.0.0'";
            }

            return '';
        }

        protected function isRailsProject(string $projectRoot): bool
        {
            return true;
        }

        protected function detectRailsVersion(string $projectRoot): ?string
        {
            return '7';
        }
    };

    $projectType = $detector->detect('/fake/path');

    expect($projectType->getMetadata('rails_version'))->toBe('7');
});

test('it can detect Rails project with app directories', function () {
    // Setup filesystem mock to return false for all exists calls except for app directories
    $this->filesystem->shouldReceive('exists')->andReturn(false);
    $this->filesystem->shouldReceive('exists')->with('/fake/path/app/controllers')->andReturn(true);
    $this->filesystem->shouldReceive('exists')->with('/fake/path/app/models')->andReturn(true);

    // Create a detector with our mocked filesystem
    $detector = new class($this->filesystem) extends ProjectTypeDetector
    {
        private Filesystem $mockedFilesystem;

        public function __construct(Filesystem $filesystem)
        {
            $this->mockedFilesystem = $filesystem;
            parent::__construct($filesystem);
        }

        protected function isRailsProject(string $projectRoot): bool
        {
            return true;
        }
    };

    $projectType = $detector->detect('/fake/path');

    expect($projectType->hasTrait('rails'))->toBeTrue();
    expect($projectType->hasTrait('ruby'))->toBeTrue();
});
