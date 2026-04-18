<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Verify;

class NameValidator
{
    /**
     * Validate a component name is safe and valid.
     *
     * @return array{valid: bool, message: string}
     */
    public static function validate(string $name): array
    {
        if (trim($name) === '') {
            return ['valid' => false, 'message' => 'Name cannot be empty.'];
        }

        if (preg_match('/[\/\\\\\.]{2,}|[\/\\\\]/', $name)) {
            return ['valid' => false, 'message' => 'Name cannot contain path separators or traversal characters.'];
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            return ['valid' => false, 'message' => 'Name must be a valid identifier (letters, numbers, underscores, starting with a letter or underscore).'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a filename (for configs, views, migrations, seeds).
     *
     * @return array{valid: bool, message: string}
     */
    public static function validateFilename(string $name): array
    {
        if (trim($name) === '') {
            return ['valid' => false, 'message' => 'Name cannot be empty.'];
        }

        if (preg_match('/[\/\\\\\.]{2,}|[\/\\\\]/', $name)) {
            return ['valid' => false, 'message' => 'Name cannot contain path separators or traversal characters.'];
        }

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $name)) {
            return ['valid' => false, 'message' => 'Name must contain only letters, numbers, underscores, hyphens, and dots.'];
        }

        return ['valid' => true, 'message' => ''];
    }
}
