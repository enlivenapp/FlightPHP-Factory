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

class CommandBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $namespace, string $targetDir): array
    {
        if (!preg_match('/Command$/', $name)) {
            $name .= 'Command';
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
        $ns->addUse('flight\\commands\\AbstractBaseCommand');

        $class = new ClassType($name);
        $class->setExtends('flight\\commands\\AbstractBaseCommand');

        $commandSlug = strtolower(preg_replace('/Command$/', '', $name));
        $commandSlug = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $commandSlug));

        $constructor = $class->addMethod('__construct')
            ->setVisibility('public')
            ->setBody(
                "parent::__construct('{$commandSlug}', '{$name} description', \$config);"
            );
        $constructor->addParameter('config')
            ->setType('array');

        $class->addMethod('execute')
            ->setVisibility('public')
            ->setReturnType('void')
            ->setBody('$io = $this->app()->io();' . "\n" . '$io->write(\'Hello from ' . $name . '\', true);');

        $ns->add($class);
        $file->addNamespace($ns);

        $printer = new PsrPrinter();
        file_put_contents($filePath, $printer->printFile($file));

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
