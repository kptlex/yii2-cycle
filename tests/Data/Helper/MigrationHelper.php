<?php

namespace Lex\Yii2\Cycle\Tests\Data\Helper;

use function dirname;
use function in_array;

class MigrationHelper
{
    public static function getNewMigration(): ?string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Migration';
        $dir = scandir($directory);
        foreach ($dir as $item) {
            if (!in_array($item, ['.', '..', 'V20201226180357_create_test_table.php', 'NoMigration.php'])) {
                return $directory . DIRECTORY_SEPARATOR . $item;
            }
        }
        return null;
    }

    public static function breakMigration($migrationFile)
    {
        if ($migrationFile) {
            $newMigrationContent = file_get_contents($migrationFile);

            $migration = str_replace('public function up(): void', 'public function up(): void
    { 
        throw new \Exception("test"); 
    } 
    
    public function test(): void', $newMigrationContent);

            file_put_contents($migrationFile, $migration);
        }
    }
}