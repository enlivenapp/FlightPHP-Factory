<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Builders;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

class ControllerBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $namespace, string $targetDir, string $type = 'web'): array
    {
        if (!preg_match('/Controller$/', $name)) {
            $name .= 'Controller';
        }

        $filePath = $targetDir . $name . '.php';

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$name} already exists at {$filePath}"];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file = new PhpFile();
        $file->setStrictTypes();

        $ns = new PhpNamespace($namespace);
        $ns->addUse('flight\\Engine');

        $class = new ClassType($name);

        $class->addProperty('app')
            ->setVisibility('protected')
            ->setType('flight\\Engine');

        $constructor = $class->addMethod('__construct')
            ->setVisibility('public')
            ->setBody('$this->app = $app;');
        $constructor->addParameter('app')
            ->setType('flight\\Engine');

        if ($type === 'api') {
            $class->addMethod('index')
                ->setVisibility('public')
                ->setBody('$this->app->json([\'status\' => \'ok\']);');
        } else {
            $class->addMethod('index')
                ->setVisibility('public')
                ->setBody('$this->app->render(\'index\');');
        }

        $ns->add($class);
        $file->addNamespace($ns);

        $printer = new PsrPrinter();
        file_put_contents($filePath, $printer->printFile($file));

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
