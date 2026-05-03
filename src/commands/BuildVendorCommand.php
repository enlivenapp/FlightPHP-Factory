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

class BuildVendorCommand extends AbstractBaseCommand
{
    protected array $components = [
        'command' => 'Command',
        'config' => 'Configuration file',
        'controller' => 'Controller',
        'middleware' => 'Middleware',
        'migration' => 'Database migration',
        'model' => 'ActiveRecord model',
        'mvc' => 'Controller + Model + View',
        'seed' => 'Database seeder',
        'service' => 'Service',
        'util' => 'Utility class',
        'view' => 'View template',
    ];

    public function __construct(array $config)
    {
        parent::__construct('build:vendor', 'Create components for a vendor package', $config);
        $this->argument('[package]', 'Vendor/package name (e.g. enlivenapp/my-plugin)');
        $this->argument('[component]', 'What to build (see list below)');
        $this->argument('[name]', 'Name of the component');
        $this->option('--type', 'Component type: web or api (controllers only)', null, null);

        $this->usage(
            '<bold>Interactively create components in a vendor package.</end><eol/>' .
            '<eol/>' .
            '<bold>Available components:</end><eol/>' .
            '<comment>  command            CLI command (src/commands/)</end><eol/>' .
            '<comment>  config             Configuration file (src/Config/)</end><eol/>' .
            '<comment>  controller         Web or API controller (src/Controllers/)</end><eol/>' .
            '<comment>  middleware          Middleware class (src/Middlewares/)</end><eol/>' .
            '<comment>  migration          Database migration (src/Database/Migrations/)</end><eol/>' .
            '<comment>  model              ActiveRecord model (src/Models/)</end><eol/>' .
            '<comment>  mvc                Controller + Model + View combo</end><eol/>' .
            '<comment>  seed               Database seeder (src/Seeds/)</end><eol/>' .
            '<comment>  service            Service class (src/Services/)</end><eol/>' .
            '<comment>  util               Utility class (src/Utils/)</end><eol/>' .
            '<comment>  view               View template (src/Views/)</end><eol/>' .
            '<eol/>' .
            '<bold>Usage:</end><eol/>' .
            '<comment>  php runway build:vendor [package] [component] [name] [--type=web|api]</end><eol/>' .
            '<eol/>' .
            '<bold>Examples:</end><eol/>' .
            '<comment>  ... build:vendor                                                  Interactive mode</end><eol/>' .
            '<comment>  ... build:vendor enlivenapp/my-plugin controller UserController    Create a web controller</end><eol/>' .
            '<comment>  ... build:vendor enlivenapp/my-plugin mvc Blog                     Create controller, model, and view</end><eol/>' .
            '<eol/>' .
            '<comment>If the package doesn\'t exist, you will be prompted to create a new one.</end><eol/>' .
            '<comment>New packages can optionally be created as Flight School plugins.</end><eol/>' .
            '<comment>https://github.com/enlivenapp/FlightPHP-Flight-School</end>'
        );
    }

    public function execute(?string $package = null, ?string $component = null, ?string $name = null): void
    {
        $io = $this->app()->io();
        $isNew = false;

        if (!$package) {
            [$package, $isNew] = $this->resolvePackage($io);
            if (!$package) {
                return;
            }
        }

        $parts = explode('/', $package);
        if (count($parts) !== 2) {
            $io->error('Package name must be in vendor/package format (e.g. enlivenapp/my-plugin)', true);
            return;
        }

        [$vendor, $pkgName] = $parts;
        $packagePath = realpath($this->projectRoot) . '/vendor/' . $vendor . '/' . $pkgName;

        if (is_dir($packagePath) && !$component) {
            $io->warn("{$package} already exists. Adding or modifying files in an existing package can break it. Only continue if you are the author of this package.", true);
            if (!$io->confirm('Continue?', 'n')) {
                return;
            }
        }

        if (!is_dir($packagePath)) {
            if (!$isNew && !$io->confirm("Package {$package} doesn't exist. Create it?")) {
                return;
            }
            $this->scaffoldPackage($io, $vendor, $pkgName, $packagePath);

            if (!$component && !$io->confirm('Create a component now?')) {
                return;
            }
        }

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

        $this->buildComponent($io, $component, $name, $vendor, $pkgName, $packagePath);
    }

    /**
     * Prompt the user to select an existing package or create a new one.
     *
     * @param object $io CLI I/O helper
     * @return array{?string, bool} [package name, is new]
     */
    protected function resolvePackage($io): array
    {
        $vendorDir = $this->projectRoot . '/vendor';
        $packages = [];

        if (is_dir($vendorDir)) {
            foreach (glob($vendorDir . '/*/*/composer.json') as $composerFile) {
                $json = json_decode(file_get_contents($composerFile), true);
                if (isset($json['name'])) {
                    $packages[$json['name']] = $json['description'] ?? '';
                }
            }
        }

        if (!empty($packages)) {
            $packages['_new'] = 'Create a new package';
            $maxLen = max(array_map('strlen', array_keys($packages)));

            $io->question('Select a package or create new');
            foreach ($packages as $name => $desc) {
                if (strlen($desc) > 70) {
                    $desc = substr($desc, 0, 70) . '...';
                }
                $io->eol()->choice(str_pad("  [{$name}]", $maxLen + 6))->answer($desc);
            }
            $io->eol();

            $choice = $io->prompt('Package');

            if ($choice !== '_new') {
                return [$choice, false];
            }
        }

        $package = $io->prompt('Vendor/package name (e.g. enlivenapp/my-plugin)');
        return [$package ?: null, true];
    }

    /**
     * Create the directory structure and boilerplate files for a new package.
     *
     * @param object $io          CLI I/O helper
     * @param string $vendor      Vendor name
     * @param string $name        Package name
     * @param string $packagePath Absolute path for the new package
     * @return void
     */
    protected function scaffoldPackage($io, string $vendor, string $name, string $packagePath): void
    {
        $namespace = $this->buildNamespace($vendor, $name);
        if (interface_exists('Enlivenapp\\FlightSchool\\PluginInterface')) {
            $io->ok('Flight School is installed.', true);
        } else {
            $io->warn('Flight School is not installed (composer require enlivenapp/flight-school).', true);
        }

        $useFlightSchool = $io->confirm('Create as a Flight School plugin?');

        mkdir($packagePath . '/src', 0755, true);

        $composerData = [
            'name' => $vendor . '/' . $name,
            'description' => '',
            'type' => $useFlightSchool ? 'flightphp-plugin' : 'library',
            'license' => 'MIT',
            'authors' => [
                ['name' => $vendor, 'email' => ''],
            ],
            'require' => [
                'php' => '^8.1',
            ],
            'autoload' => [
                'psr-4' => [
                    $namespace . '\\' => 'src/',
                ],
            ],
        ];

        if ($useFlightSchool) {
            $composerData['require']['enlivenapp/flight-school'] = '^0.2';
        }

        file_put_contents(
            $packagePath . '/composer.json',
            json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );

        if ($useFlightSchool) {
            $pluginContent = <<<PHP
            <?php

            declare(strict_types=1);

            namespace {$namespace};

            use Enlivenapp\\FlightSchool\\PluginInterface;
            use flight\\Engine;
            use flight\\net\\Router;

            class Plugin implements PluginInterface
            {
                public function register(Engine \$app, Router \$router, array \$config = []): void
                {
                    // Register menu items:
                    // \$app->slot('menu.content', '{$name}', [
                    //     'label'    => '{$name}',
                    //     'url'      => '/admin/{$name}',
                    //     'icon'     => 'ti-point',
                    //     'priority' => 50,
                    // ]);

                    // Register a tab on another page:
                    // \$app->slot('users.edit.tabs', '{$name}', [
                    //     'label'    => '{$name}',
                    //     'priority' => 50,
                    //     'callable' => function (array \$context) use (\$app) {
                    //         return [
                    //             'fields'     => [
                    //                 'field_name' => ['value' => '', 'type' => 'string', 'title' => 'Field Label'],
                    //             ],
                    //             'post_url'   => '/admin/{$name}/' . \$context['user_id'] . '/update',
                    //             'return_url' => \$context['return_url'] ?? '',
                    //         ];
                    //     },
                    // ]);
                }
            }
            PHP;

            file_put_contents($packagePath . '/src/Plugin.php', $pluginContent . "\n");

            // Create Config directory with boilerplate files
            $configDir = $packagePath . '/src/Config';
            mkdir($configDir, 0755, true);

            $configContent = <<<PHP
            <?php

            /**
             * @package   {$namespace}
             * @license   MIT
             */

            return [
                // 'configPrepend' => '{$vendor}.{$name}',
                // 'routePrepend' => '{$name}',
            ];
            PHP;

            file_put_contents($configDir . '/Config.php', $configContent . "\n");

            $routesContent = <<<PHP
            <?php

            /**
             * Public routes.
             *
             * Auto-prefixed by Flight School using the routePrepend value from Config.php.
             */

            /** @var \\flight\\net\\Router \$router */
            /** @var \\flight\\Engine \$app */
            /** @var string \$configPrepend */
            PHP;

            file_put_contents($configDir . '/Routes.php', $routesContent . "\n");

            $adminRoutesContent = <<<PHP
            <?php

            /**
             * Admin routes.
             *
             * Auto-prefixed with /admin by Flight School.
             */

            // use Enlivenapp\\FlightCsrf\\Middlewares\\CsrfMiddleware;
            // use Enlivenapp\\FlightShield\\Middlewares\\SessionAuthMiddleware;

            /** @var \\flight\\net\\Router \$router */
            /** @var \\flight\\Engine \$app */
            /** @var string \$configPrepend */
            PHP;

            file_put_contents($configDir . '/AdminRoutes.php', $adminRoutesContent . "\n");
        }

        $io->ok("{$vendor}/{$name} has been created at {$packagePath}", true);
    }

    /**
     * Build a single component inside a vendor package and report the result.
     *
     * @param object $io          CLI I/O helper
     * @param string $component   Component type key (e.g. 'controller', 'model')
     * @param string $name        Name for the new component
     * @param string $vendor      Vendor name
     * @param string $pkgName     Package name
     * @param string $packagePath Absolute path to the package directory
     * @return void
     */
    protected function buildComponent($io, string $component, string $name, string $vendor, string $pkgName, string $packagePath): void
    {
        $namespace = $this->buildNamespace($vendor, $pkgName);

        switch ($component) {
            case 'command':
                $targetDir = $packagePath . '/src/commands/';
                $check = ComponentExists::check($name, 'Command', $targetDir);
                if ($check['exists']) {
                    $io->error("{$check['name']} already exists at {$check['path']}", true);
                    return;
                }
                $result = (new CommandBuilder())->build($name, $namespace . '\\Commands', $targetDir);
                break;

            case 'config':
                $targetDir = $packagePath . '/src/Config/';
                $result = (new ConfigBuilder())->build($name, $targetDir, true);
                break;

            case 'controller':
                $targetDir = $packagePath . '/src/Controllers/';
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
                $result = (new ControllerBuilder())->build($name, $namespace . '\\Controllers', $targetDir, $type);
                break;

            case 'middleware':
                $targetDir = $packagePath . '/src/Middlewares/';
                $check = ComponentExists::check($name, 'Middleware', $targetDir);
                if ($check['exists']) {
                    $io->error("{$check['name']} already exists at {$check['path']}", true);
                    return;
                }
                $result = (new MiddlewareBuilder())->build($name, $namespace . '\\Middlewares', $targetDir);
                break;

            case 'migration':
                $targetDir = $packagePath . '/src/Database/Migrations/';
                $result = (new MigrationBuilder())->build($name, $targetDir, $namespace . '\\Database\\Migrations');
                break;

            case 'model':
                $targetDir = $packagePath . '/src/Models/';
                $result = (new ModelBuilder())->build($name, $namespace . '\\Models', $targetDir);
                break;

            case 'mvc':
                $this->buildMvc($io, $name, $vendor, $pkgName, $packagePath);
                return;

            case 'seed':
                $targetDir = $packagePath . '/src/Seeds/';
                $result = (new SeedBuilder())->build($name, $targetDir);
                break;

            case 'service':
                $targetDir = $packagePath . '/src/Services/';
                $result = (new ServiceBuilder())->build($name, $namespace . '\\Services', $targetDir);
                break;

            case 'util':
                $targetDir = $packagePath . '/src/Utils/';
                $result = (new UtilBuilder())->build($name, $namespace . '\\Utils', $targetDir);
                break;

            case 'view':
                $targetDir = $packagePath . '/src/Views/';
                $result = (new ViewBuilder())->build($name, $targetDir);
                break;
        }

        if ($result['success']) {
            $io->ok($result['message'], true);
        } else {
            $io->error($result['message'], true);
        }
    }

    /**
     * Build a controller + model + view combo inside a vendor package.
     *
     * @param object $io          CLI I/O helper
     * @param string $name        Base name for the MVC set
     * @param string $vendor      Vendor name
     * @param string $pkgName     Package name
     * @param string $packagePath Absolute path to the package directory
     * @return void
     */
    protected function buildMvc($io, string $name, string $vendor, string $pkgName, string $packagePath): void
    {
        $namespace = $this->buildNamespace($vendor, $pkgName);

        $type = $this->type;
        if (!$type) {
            $type = $io->choice('Controller type', [
                'web' => 'Web (HTML responses)',
                'api' => 'API (JSON responses)',
            ]);
        }

        $controllerDir = $packagePath . '/src/Controllers/';
        $check = ComponentExists::check($name, 'Controller', $controllerDir);
        if ($check['exists']) {
            $io->error("{$check['name']} already exists at {$check['path']}", true);
            return;
        }

        $result = (new ControllerBuilder())->build($name, $namespace . '\\Controllers', $controllerDir, $type);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);

        $modelDir = $packagePath . '/src/Models/';
        $result = (new ModelBuilder())->build($name, $namespace . '\\Models', $modelDir);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);

        $viewDir = $packagePath . '/src/Views/';
        $viewName = strtolower(preg_replace('/Controller$/', '', $name));
        $result = (new ViewBuilder())->build($viewName, $viewDir);
        $io->{$result['success'] ? 'ok' : 'error'}($result['message'], true);
    }

    protected function buildNamespace(string $vendor, string $name): string
    {
        $vendorNs = str_replace(' ', '', ucwords(str_replace('-', ' ', $vendor)));
        $nameNs = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
        return $vendorNs . '\\' . $nameNs;
    }
}
