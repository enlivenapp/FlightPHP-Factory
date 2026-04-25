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

class EntityBuilder
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

        // Derive table name: "UserProfile" → "user_profiles"
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        if (preg_match('/[^aeiou]y$/', $tableName)) {
            $tableName = substr($tableName, 0, -1) . 'ies';
        } elseif (preg_match('/(s|x|z|ch|sh)$/', $tableName)) {
            $tableName .= 'es';
        } elseif (!str_ends_with($tableName, 's')) {
            $tableName .= 's';
        }

        $file = new PhpFile();
        $file->setStrictTypes();

        $ns = new PhpNamespace($namespace);
        $ns->addUse('flight\\ActiveRecord');

        $class = new ClassType($name);
        $class->setExtends('flight\\ActiveRecord');

        $constructor = $class->addMethod('__construct')
            ->setVisibility('public');
        $constructor->addParameter('pdo')
            ->setDefaultValue(null);
        $constructor->addParameter('config')
            ->setType('array')
            ->setDefaultValue([]);
        $constructor->setBody("parent::__construct(\$pdo, '{$tableName}', \$config);");

        $ns->add($class);
        $file->addNamespace($ns);

        $printer = new PsrPrinter();
        file_put_contents($filePath, $printer->printFile($file));

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
