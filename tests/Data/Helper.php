<?php

namespace Cycle\Tests\Data\Helper;

use PHPUnit\Framework\TestCase;

use function in_array;

class Helper extends TestCase
{
    /**
     * @uses \Lex\Yii2\Cycle\Tests\MigrationTest::testMigrationUp()
     */
    public static function getAndUpdateNewMigrationFile()
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'Migration';
        $dir = scandir($directory);
        foreach ($dir as $item) {
            if (!in_array($item, ['.', '..', 'V20201226180357_create_test_table.php'])) {
                $newMigrationFile = $directory . DIRECTORY_SEPARATOR . $item;
            }
        }

        if ($newMigrationFile) {
            self::assertNotEmpty($newMigrationFile);
            $newMigrationContent = file_get_contents($newMigrationFile);

            $migration = str_replace('public function up(): void', 'public function up(): void
    { 
        throw new \Exception("test"); 
    } 
    
    public function test(): void', $newMigrationContent);

            file_put_contents($newMigrationFile, $migration);
        }
    }
}