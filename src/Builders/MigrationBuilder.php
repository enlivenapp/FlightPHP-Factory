<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Builders;

class MigrationBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $targetDir): array
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Determine next sequence number
        $existing = glob($targetDir . '*.sql');
        $next = 1;
        if (!empty($existing)) {
            foreach ($existing as $file) {
                $basename = basename($file);
                if (preg_match('/^(\d+)_/', $basename, $matches)) {
                    $num = (int) $matches[1];
                    if ($num >= $next) {
                        $next = $num + 1;
                    }
                }
            }
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
        $filename = str_pad((string) $next, 3, '0', STR_PAD_LEFT) . '_' . $slug . '.sql';
        $filePath = $targetDir . $filename;

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$filename} already exists at {$filePath}"];
        }

        $content = "-- Migration: {$name}\n\n";

        file_put_contents($filePath, $content);

        return ['success' => true, 'message' => "{$filename} has been created at {$filePath}"];
    }
}
