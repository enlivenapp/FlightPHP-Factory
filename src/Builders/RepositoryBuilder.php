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

class RepositoryBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $namespace, string $targetDir, ?string $entityNamespace = null): array
    {
        if (!preg_match('/Repository$/', $name)) {
            $name .= 'Repository';
        }

        $filePath = $targetDir . $name . '.php';

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$name} already exists at {$filePath}"];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Derive entity name from repository name: "ProfileRepository" → "Profile"
        $entityName = preg_replace('/Repository$/', '', $name);

        $file = new PhpFile();
        $file->setStrictTypes();

        $ns = new PhpNamespace($namespace);

        if ($entityNamespace) {
            $entityFqcn = $entityNamespace . '\\' . $entityName;
            $ns->addUse($entityFqcn);
        }

        $class = new ClassType($name);

        $class->addProperty('pdo')
            ->setVisibility('protected')
            ->setType('\\PDO');

        $constructor = $class->addMethod('__construct')
            ->setVisibility('public')
            ->setBody('$this->pdo = $pdo;');
        $constructor->addParameter('pdo')
            ->setType('\\PDO');

        // findById
        $findById = $class->addMethod('findById')
            ->setVisibility('public')
            ->setReturnNullable()
            ->setReturnType($entityNamespace ? $entityFqcn : 'object');
        $findById->addParameter('id')
            ->setType('int');
        $findById->setBody(
            "\${$this->lcfirst($entityName)} = new {$entityName}(\$this->pdo);\n" .
            "\${$this->lcfirst($entityName)}->eq('id', \$id)->find();\n\n" .
            "return \${$this->lcfirst($entityName)}->isHydrated() ? \${$this->lcfirst($entityName)} : null;"
        );

        $ns->add($class);
        $file->addNamespace($ns);

        $printer = new PsrPrinter();
        file_put_contents($filePath, $printer->printFile($file));

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }

    private function lcfirst(string $str): string
    {
        return lcfirst($str);
    }
}
