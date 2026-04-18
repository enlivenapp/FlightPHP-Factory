<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Commands;

use Enlivenapp\FlightFactory\Builders\CommandBuilder;
use Enlivenapp\FlightFactory\Builders\ConfigBuilder;
use Enlivenapp\FlightFactory\Builders\ControllerBuilder;
use Enlivenapp\FlightFactory\Builders\MiddlewareBuilder;
use Enlivenapp\FlightFactory\Builders\MigrationBuilder;
use Enlivenapp\FlightFactory\Builders\ModelBuilder;
use Enlivenapp\FlightFactory\Builders\SeedBuilder;
use Enlivenapp\FlightFactory\Builders\ServiceBuilder;
use Enlivenapp\FlightFactory\Builders\UtilBuilder;
use Enlivenapp\FlightFactory\Builders\ViewBuilder;
use Enlivenapp\FlightFactory\Verify\ComponentExists;
use Enlivenapp\FlightFactory\Verify\NameValidator;
use flight\commands\AbstractBaseCommand;

class BuildAppCommand extends AbstractBaseCommand
{
    protected array $components = [
        'command' => 'Command',
        'config' => 'Configuration file',
        'controller' => 'Controller',
        'middleware' => 'Middleware',
        'migration' => 'Database migration',
        'model' => 'Model',
        'mvc' => 'Controller + Model + View',
        'seed' => 'Database seeder',
        'service' => 'Service',
        'util' => 'Utility class',
        'view' => 'View template',
    ];

    public function __construct(array $config)
    {
        parent::__construct('build:app', 'Create components for your application', $config);
        $this->argument('[component]', 'What to build (see list below)');
        $this->argument('[name]', 'Name of the component');
        $this->option('--type', 'Component type: web or api (controllers only)', null, null);

        $this->usage(
            '<bold>Interactively create components in your app/ directory.</end><eol/>' .
            '<eol/>' .
            '<bold>Available components:</end><eol/>' .
            '<comment>  command            CLI command (app/commands/)</end><eol/>' .
            '<comment>  config             Configuration file (app/config/)</end><eol/>' .
            '<comment>  controller         Web or API controller (app/controllers/)</end><eol/>' .
            '<comment>  middleware          Middleware class (app/middlewares/)</end><eol/>' .
            '<comment>  migration          Database migration (app/migrations/)</end><eol/>' .
            '<comment>  model              Model class (app/models/)</end><eol/>' .
            '<comment>  mvc                Controller + Model + View combo</end><eol/>' .
            '<comment>  seed               Database seeder (app/seeds/)</end><eol/>' .
            '<comment>  service            Service class (app/services/)</end><eol/>' .
            '<comment>  util               Utility class (app/utils/)</end><eol/>' .
            '<comment>  view               View template (app/views/)</end><eol/>' .
            '<eol/>' .
            '<bold>Usage:</end><eol/>' .
            '<comment>  php runway build:app [component] [name] [--type=web|api]</end><eol/>' .
            '<eol/>' .
            '<bold>Examples:</end><eol/>' .
            '<comment>  ... build:app                                    Interactive mode</end><eol/>' .
            '<comment>  ... build:app controller UserController          Create a web controller</end><eol/>' .
            '<comment>  ... build:app mvc Blog                           Create controller, model, and view</end><eol/>' .
            '<eol/>' .
            '<comment>Generated files are placed in the app/ directory using the app\\ namespace.</end>'
        );
    }

    public function execute(?string $component = null, ?string $name = null): void
    {
        $io = $this->app()->io();

        $runwayConfig = $this->config['runway'] ?? [];
        $appRoot = $runwayConfig['app_root'] ?? 'app/';

        if (!$component) {
            $component = $io->choice('What would you like to build?', $this->components);
        }

        if (!isset($this->components[$component])) {
            $io->error('Unknown component: ' . $component, true);
            return;
        }

        if (!$name) {
            $name = $io->prompt('Name');
        }

        $isFile = in_array($component, ['config', 'migration', 'seed', 'view'], true);
        $check = $isFile ? NameValidator::validateFilename($name) : NameValidator::validate($name);
        if (!$check['valid']) {
            $io->error($check['message'], true);
            return;
        }

        if ($this->type && !in_array($this->type, ['web', 'api'], true)) {
            $io->error("Invalid type '{$this->type}'. Must be 'web' or 'api'.", true);
            return;
        }

        $this->buildComponent($io, $component, $name, $appRoot);
    }

    protected function buildComponent($io, string $component, string $name, string $appRoot): void
    {
        $projectRoot = realpath($this->projectRoot) . '/' . $appRoot;

        switch ($component) {
            case 'command':
                $targetDir = $projectRoot . 'commands/';
                $check = ComponentExists::check($name, 'Command', $targetDir);
                if ($check['exists']) {
                    $io->error("{$check['name']} already exists at {$check['path']}", true);
                    return;
                }
                $result = (new CommandBuilder())->build($name, 'app\\commands', $targetDir);
                break;

            case 'config':
                $targetDir = $projectRoot . 'config/';
                $result = (new ConfigBuilder())->build($name, $targetDir);
                break;

            case 'controller':
                $targetDir = $projectRoot . 'controllers/';
                $check = ComponentExists::check($name, 'Controller', $targetDir);
                if ($check['exists']) {
                    $io->error("{$check['name']} already exists at {$check['path']}", true);
                    return;
                }
                $type = $this->type;
                if (!$type) {
                    $type = $io->choice('Controller type', [
                        'web' => 'Web (HTML responses)',
                        'api' => 'API (JSON responses)',
                    ]);
                }
                $result = (new ControllerBuilder())->build($name, 'app\\controllers', $targetDir, $type);
                break;

            case 'middleware':
                $targetDir = $projectRoot . 'middlewares/';
                $check = ComponentExists::check($name, 'Middleware', $targetDir);
                if ($check['exists']) {
                    $io->error("{$check['name']} already exists at {$check['path']}", true);
                    return;
                }
                $result = (new MiddlewareBuilder())->build($name, 'app\\middlewares', $targetDir);
                break;

            case 'migration':
                $targetDir = $projectRoot . 'migrations/';
                $result = (new MigrationBuilder())->build($name, $targetDir);
                break;

            case 'model':
                $targetDir = $projectRoot . 'models/';
                $result = (new ModelBuilder())->build($name, 'app\\models', $targetDir);
                break;

            case 'mvc':
                $this->buildMvc($io, $name, $projectRoot);
                return;

            case 'seed':
                $targetDir = $projectRoot . 'seeds/';
                $result = (new SeedBuilder())->build($name, $targetDir);
                break;

            case 'service':
                $targetDir = $projectRoot . 'services/';
                $result = (new ServiceBuilder())->build($name, 'app\\services', $targetDir);
                break;

            case 'util':
                $targetDir = $projectRoot . 'utils/';
                $result = (new UtilBuilder())->build($name, 'app\\utils', $targetDir);
                break;

            case 'view':
                $targetDir = $projectRoot . 'views/';
                $result = (new ViewBuilder())->build($name, $targetDir);
                break;
        }

        if ($result['success']) {
            $io->ok($result['message'], true);
        } else {
            $io->error($result['message'], true);
        }
    }

    protected function buildMvc($io, string $name, string $projectRoot): void
    {
        $type = $this->type;
        if (!$type) {
            $type = $io->choice('Controller type', [
                'web' => 'Web (HTML responses)',
                'api' => 'API (JSON responses)',
            ]);
        }

        $controllerDir = $projectRoot . 'controllers/';
        $check = ComponentExists::check($name, 'Controller', $controllerDir);
        if ($check['exists']) {
            $io->error("{$check['name']} already exists at {$check['path']}", true);
            return;
        }

        $result = (new ControllerBuilder())->build($name, 'app\\controllers', $controllerDir, $type);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);

        $modelDir = $projectRoot . 'models/';
        $result = (new ModelBuilder())->build($name, 'app\\models', $modelDir);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);

        $viewDir = $projectRoot . 'views/';
        $viewName = strtolower(preg_replace('/Controller$/', '', $name));
        $result = (new ViewBuilder())->build($viewName, $viewDir);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);
    }
}
