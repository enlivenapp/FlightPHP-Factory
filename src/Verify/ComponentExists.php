<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Verify;

class ComponentExists
{
    /**
     * @return array{exists: bool, name: string, path: string}
     */
    public static function check(string $name, string $suffix, string $targetDir): array
    {
        $checkName = $name;
        if (!preg_match('/' . $suffix . '$/', $checkName)) {
            $checkName .= $suffix;
        }

        $filePath = $targetDir . $checkName . '.php';

        return [
            'exists' => file_exists($filePath),
            'name' => $checkName,
            'path' => $filePath,
        ];
    }
}
