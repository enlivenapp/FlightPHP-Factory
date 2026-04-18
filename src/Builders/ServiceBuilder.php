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

class ServiceBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $namespace, string $targetDir): array
    {
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

        $class = new ClassType($name);

        $constructor = $class->addMethod('__construct')
            ->setVisibility('public');

        $ns->add($class);
        $file->addNamespace($ns);

        $printer = new PsrPrinter();
        file_put_contents($filePath, $printer->printFile($file));

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
