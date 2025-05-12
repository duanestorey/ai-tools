<?php

namespace DuaneStorey\AiTools\Console\Command;

use DuaneStorey\AiTools\Core\Configuration;
use DuaneStorey\AiTools\Core\ProjectFinder;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitConfigCommand extends Command
{
    /**
     * The default name of the command
     *
     * @var string
     */
    protected static $defaultName = 'init';

    /**
     * The default description of the command
     *
     * @var string
     */
    protected static $defaultDescription = 'Create an example .ai-tools.json configuration file';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('<fg=green>duanestorey/ai-tools v%s</>', Version::get()));

        // Find project root
        $projectFinder = new ProjectFinder;
        $projectRoot = $projectFinder->findProjectRoot();

        if (! $projectRoot) {
            $io->error('Could not determine project root. Make sure you run this command from a valid project directory.');

            return Command::FAILURE;
        }

        $io->text(sprintf('Project root: <info>%s</info>', $projectRoot));

        // Create configuration file
        $config = new Configuration($projectRoot);
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
}
