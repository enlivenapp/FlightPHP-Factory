<?php

/**
 * @package   Enlivenapp\FlightFactory
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightFactory\Builders;

class ConfigBuilder
{
    /**
     * @return array{success: bool, message: string}
     */
    public function build(string $name, string $targetDir, bool $isVendor = false): array
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

        if ($isVendor) {
            $content = <<<'PHP'
            <?php

            /**
             * Plugin configuration.
             *
             * configPrepend — the key your config is stored under on $app.
             *                 Default: vendor.package-name (derived from composer name)
             * routePrepend  — the URL prefix for all public routes in Routes.php.
             *                 AdminRoutes.php is always prefixed with /admin.
             *
             * Add these keys to the returned array to override the defaults:
             */

            return [
                // 'configPrepend' => 'vendor.package-name',
                // 'routePrepend' => 'package-name',
            ];
            PHP;
        } else {
            $content = <<<'PHP'
            <?php

            /**
             * Configuration file.
             */

            return [
            ];
            PHP;
        }

        file_put_contents($filePath, $content . "\n");

        return ['success' => true, 'message' => "{$name} has been created at {$filePath}"];
    }
}
