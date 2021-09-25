<?php

namespace Lex\Yii2\Cycle\Tests;

use Cycle\ORM\ORMInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Spiral\Migrations\Migrator;
use Yii;
use Lex\Yii2\Cycle\Command\MigrateCommand;
use Lex\Yii2\Cycle\Factory\OrmFactory;
use Lex\Yii2\Cycle\Tests\Data\Helper\MigrationHelper;

final class MigrationTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function before() {
        $ormFactory = Yii::$container->get(OrmFactory::class);
        $ormFactory->bootstrap(Yii::$app);
    }

    public function testCreate()
    {
        $this->getMigrator()->actionCreate('second_test');
        $newMigrationFile = MigrationHelper::getNewMigration();
        self::assertNotEmpty($newMigrationFile);
        MigrationHelper::breakMigration($newMigrationFile);
        return $newMigrationFile;
    }

    public function testMigrator()
    {
        $migrator = Yii::$container->get(Migrator::class);
        self::assertInstanceOf(Migrator::class, $migrator);
    }

    private function getMigrator(): MigrateCommand
    {
        /**
         * @var MigrateCommand $migrator
         */
        $migrator = Yii::$app->createController('migrator')[0];
        $migrator->interactive = false;
        return $migrator;
    }

    protected function migrationUp()
    {
        $migrator = Yii::$container->get(Migrator::class);
        self::assertInstanceOf(Migrator::class, $migrator);

        $this->getMigrator()->actionIndex();
        /**
         * @var ORMInterface $orm
         */
        $orm = Yii::$container->get(ORMInterface::class);
        $table = $orm->getFactory()->database()->table('test');

        self::assertTrue($table->exists());
    }

    /**
     * @depends testCreate
     */
    public function testMigrationUp(?string $newMigration)
    {
        if ($newMigration) {
            try {
                $this->migrationUp();
            } catch (Exception $e) {
                self::assertNotEmpty($e->getMessage());
                unlink($newMigration);
                $this->testMigrationUp(MigrationHelper::getNewMigration());
            }
        } else {
            $this->migrationUp();
        }
    }

    /**
     * @depends testMigrationUp
     */
    public function testMigrationDown()
    {
        $this->getMigrator()->actionDown();
        /**
         * @var ORMInterface $orm
         */
        $orm = Yii::$container->get(ORMInterface::class);
        $table = $orm->getFactory()->database()->table('test');
        self::assertNotTrue($table->exists());
    }
}