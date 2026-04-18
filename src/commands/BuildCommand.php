<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Commands;

use flight\commands\AbstractBaseCommand;

class BuildCommand extends AbstractBaseCommand
{
    public function __construct(array $config)
    {
        parent::__construct('build', 'Flight Factory — interactive scaffolding tool', $config);

        $this->usage(
            '<bold>Available commands:</end><eol/>' .
            '<eol/>' .
            '<bold>  build:app</end>          <comment>Create components for your application</end><eol/>' .
            '<bold>  build:vendor</end>       <comment>Create components for a vendor package</end><eol/>' .
            '<eol/>' .
            '<comment>Run any command without arguments for interactive mode.</end><eol/>' .
            '<comment>Run any command with --help for usage details and examples.</end>'
        );
    }

    public function execute(): void
    {
        $this->showHelp();
    }
}
