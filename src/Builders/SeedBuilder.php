<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Builders;

class SeedBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $targetDir): array
    {
        if (!preg_match('/\.sql$/', $name)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
            $name = $slug . '.sql';
        }

        $filePath = $targetDir . $name;

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$name} already exists at {$filePath}"];
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $baseName = basename($name, '.sql');
        $content = "-- Seed: {$baseName}\n\n";

        file_put_contents($filePath, $content);

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
