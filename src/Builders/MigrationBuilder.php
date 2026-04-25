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
    public function build(string $name, string $targetDir, string $namespace = 'App\\Database\\Migrations'): array
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Determine next sequence number from existing migrations
        $existing = glob($targetDir . '*.php');
        $maxSeq = 0;
        $today = date('Y-m-d');
        foreach ($existing as $file) {
            $basename = basename($file, '.php');
            if (preg_match('/^(\d{4}-\d{2}-\d{2})-(\d{6})_/', $basename, $matches)) {
                if ($matches[1] === $today && (int) $matches[2] > $maxSeq) {
                    $maxSeq = (int) $matches[2];
                }
            }
        }

        $sequence = str_pad((string) ($maxSeq + 1), 6, '0', STR_PAD_LEFT);
        $timestamp = $today . '-' . $sequence;

        $className = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
        $filename = $timestamp . '_' . $className . '.php';
        $filePath = $targetDir . $filename;

        if (file_exists($filePath)) {
            return ['success' => false, 'message' => "{$filename} already exists at {$filePath}"];
        }

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Enlivenapp\Migrations\Services\Migration;

        class {$className} extends Migration
        {
            public function up(): void
            {
                // \$this->table('table_name')
                //     ->addColumn('id', 'primary', [])
                //     ->addColumn('name', 'string', ['length' => 255])
                //     ->addColumn('created_at', 'datetime', ['nullable' => true, 'default' => null])
                //     ->addColumn('updated_at', 'datetime', ['nullable' => true, 'default' => null])
                //     ->create();
            }

            public function down(): void
            {
                // \$this->table('table_name')->drop();
            }
        }
        PHP;

        file_put_contents($filePath, $content . "\n");

        return ['success' => true, 'message' => "{$filename} has been created at {$filePath}"];
    }
}
