<?php

namespace DuaneStorey\AiTools\Console;

use DuaneStorey\AiTools\Console\Command\GenerateOverviewCommand;
use DuaneStorey\AiTools\Console\Command\InitConfigCommand;
use DuaneStorey\AiTools\Core\Version;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('duanestorey/ai-tools', Version::get());

        $this->add(new GenerateOverviewCommand);
        $this->add(new InitConfigCommand);
    }
}
