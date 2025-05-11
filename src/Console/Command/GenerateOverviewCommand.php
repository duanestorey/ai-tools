<?php

namespace DuaneStorey\AiTools\Console\Command;

use DuaneStorey\AiTools\Core\OverviewGenerator;
use DuaneStorey\AiTools\Core\ProjectFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateOverviewCommand extends Command
{
    protected static $defaultName = 'generate';

    protected static $defaultDescription = 'Generate AI-friendly project overview';
    
    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and regenerate automatically')
            ->addOption('init-config', null, InputOption::VALUE_NONE, 'Create an example .ai-tools.json configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('<fg=green>duanestorey/ai-tools v1.0.0</>');

        // Find project root
        $projectFinder = new ProjectFinder;
        $projectRoot = $projectFinder->findProjectRoot();

        if (! $projectRoot) {
            $io->error('Could not determine project root. Make sure you run this command from a valid project directory.');

            return Command::FAILURE;
        }

        $io->text(sprintf('Project root: <info>%s</info>', $projectRoot));

        // Handle init-config option
        if ($input->getOption('init-config')) {
            $config = new \DuaneStorey\AiTools\Core\Configuration($projectRoot);
            if ($config->createExampleConfig($projectRoot)) {
                $io->success('Created example configuration file: .ai-tools.json');
                $io->text('You can customize this file to configure the AI overview generator.');
                $io->text('The file has been added to your .gitignore if it exists.');

                return Command::SUCCESS;
            } else {
                $io->warning('Configuration file already exists. Not overwriting.');

                return Command::SUCCESS;
            }
        }

        // Create overview generator
        $generator = new OverviewGenerator($projectRoot, $io);

        // Watch mode
        if ($input->getOption('watch')) {
            $io->note('Watch mode enabled. Press Ctrl+C to stop.');

            return $generator->watchAndGenerate();
        }

        // One-time generation
        return $generator->generate();
    }
}
