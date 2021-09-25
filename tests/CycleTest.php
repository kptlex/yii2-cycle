<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Tests;

use Spiral\Migrations\Exception\RepositoryException;
use Yii;
use PHPUnit\Framework\TestCase;
use Cycle\{ORM\ORM, ORM\ORMInterface};
use Lex\Yii2\Cycle\Factory\OrmFactory;
use Lex\Yii2\Cycle\FileRepository;
use Lex\Yii2\Cycle\Provider\CacheProvider;
use Lex\Yii2\Cycle\Provider\ProviderInterface;
use Lex\Yii2\Cycle\Tests\Migration\NoMigration;
use Lex\Yii2\Cycle\Tests\Migration\V20201226180357_create_test_table;

final class CycleTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function before()
    {
        Yii::$app->cache->delete(CacheProvider::DEFAULT_KEY);
        $dbFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database.test';
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }
    }

    public function testCycle()
    {
        /**
         * @var OrmFactory $ormFactory
         */
        $ormFactory = Yii::$container->get(OrmFactory::class);
        self::assertInstanceOf(OrmFactory::class, $ormFactory);
        $ormFactory->bootstrap(Yii::$app);

        /**
         * @var ORMInterface $orm
         */
        $orm = Yii::$container->get(ORMInterface::class);
        self::assertInstanceOf(ORM::class, $orm);
        return $ormFactory;
    }

    /**
     * @depends testCycle
     */
    public function testFileRepository()
    {
        $provider = Yii::$container->get(ProviderInterface::class);
        $repository = new FileRepository($provider->getMigrationConfig());
        try {
            $repository->registerMigration('test', 'test');
        } catch (RepositoryException $exception) {
            self::assertNotEmpty($exception->getMessage());
        }
        try {
            echo $repository->registerMigration('test', V20201226180357_create_test_table::class);
        } catch (RepositoryException $exception) {
            self::assertNotEmpty($exception->getMessage());
        }
        try {
            echo $repository->registerMigration('V20201226180357_create_test_table', NoMigration::class);
        } catch (RepositoryException $exception) {
            self::assertNotEmpty($exception->getMessage());
        }
        $file = $repository->registerMigration('V20201226180358_create_test_table', NoMigration::class);
        self::assertNotEmpty($file);
        unlink($file);
    }
}