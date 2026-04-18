<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Builders;

class ViewBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $targetDir): array
    {
        if (!preg_match('/\.php$/', $name)) {
            $name .= '.php';
        }

        $filePath = $targetDir . $name;

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$name} already exists at {$filePath}"];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $baseName = basename($name, '.php');
        $title = ucfirst($baseName);

        $content = <<<HTML
        <h1>{$title}</h1>
        HTML;

        file_put_contents($filePath, $content . "\n");

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
