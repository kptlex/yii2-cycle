<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Command;

use ReflectionException;
use Spiral\Migrations\Migration;
use Spiral\Migrations\Migrator;
use Spiral\Reactor\FileDeclaration;
use Throwable;
use Lex\Yii\Cycle\ClassDeclaration;
use Lex\Yii\Cycle\Factory\MigrationFactory;
use Lex\Yii\Cycle\Factory\OrmFactory;
use Lex\Yii\Cycle\FileRepository;
use Lex\Yii\Cycle\Provider\ProviderInterface;
use yii\helpers\Inflector;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\di\NotInstantiableException;
use yii\helpers\Console;
use Yii;

final class MigrateCommand extends Controller
{
    private Migrator $migrator;

    /**
     * MigrateCommand constructor.
     * @param $id
     * @param $module
     * @param OrmFactory $ormFactory
     * @param MigrationFactory $factory
     * @param array $config
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct($id, $module, OrmFactory $ormFactory, MigrationFactory $factory, $config = [])
    {
        $this->migrator = $factory->getMigrator($ormFactory->getDbal());
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): void
    {
        $this->actionMigrate();
    }

    public function actionMigrate(): void
    {
        if (!$this->migrator->isConfigured()) {
            $this->migrator->configure();
            $this->stdout('Migration table was created. ' . PHP_EOL, Console::FG_GREEN);
        }

        while ($migration = $this->migrator->run()) {
            /** @var Migration $migration */
            $message = sprintf('Migration %s was success.', $migration->getState()->getName()) . PHP_EOL;
            $this->stdout($message, Console::FG_YELLOW);
        }

        $this->stdout('Success' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * @param string $name
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function actionCreate(string $name): void
    {
        $config = $this->migrator->getConfig();

        $name = Inflector::tableize($name);

        $fileRepository = new FileRepository($config);
        $name = $fileRepository->createFilename($name);
        $class = new ClassDeclaration(str_replace('.php', '', basename($name)), 'Migration');
        $class->method('up')->setPublic()->setReturn('void');
        $class->method('down')->setPublic()->setReturn('void');

        $file = new FileDeclaration($config->getNamespace());
        $file->addUse(Migration::class);
        $file->addElement($class);

        $provider = Yii::$container->get(ProviderInterface::class);
        $migrationConfig = $provider->getMigrationConfig();
        $fileRepository = new FileRepository($migrationConfig);
        $fileRepository->registerMigration(
            str_replace('.php', '', basename($name)),
            str_replace('.php', '', basename($name)),
            $file->render()
        );
        $this->stdout('Migration "' . $name . '"  created' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * @param int|null $count
     * @throws Throwable
     */
    public function actionDown(?int $count = null): void
    {
        $migrator = $this->migrator;
        $iteration = 0;
        while ((($iteration++ < $count) || $count === null) && $migration = $migrator->rollback()) {
            /** @var Migration $migration */
            $message = sprintf('Migration "%s" cancelled. ', $migration->getState()->getName()) . PHP_EOL;
            $this->stdout($message, Console::FG_YELLOW);
        }
    }
}