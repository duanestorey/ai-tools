<?php

namespace DuaneStorey\AiTools\Console;

use DuaneStorey\AiTools\Console\Command\GenerateOverviewCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('duanestorey/ai-tools', '1.0.0');

        $this->add(new GenerateOverviewCommand);
    }
}
