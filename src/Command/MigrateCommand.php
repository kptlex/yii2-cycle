<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Command;

use Lex\Yii2\Cycle\ClassDeclaration;
use Lex\Yii2\Cycle\Factory\MigrationFactory;
use Lex\Yii2\Cycle\Factory\OrmFactory;
use Lex\Yii2\Cycle\FileRepository;
use Lex\Yii2\Cycle\MigrationConfig;
use ReflectionException;
use Spiral\Migrations\Migration;
use Spiral\Migrations\Migrator;
use Spiral\Reactor\FileDeclaration;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\di\NotInstantiableException;
use yii\helpers\Console;

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
    public function actionCreate(string $name, ?string $directory = null): void
    {
        $defaultConfig = $this->migrator->getConfig();
        if (!$directory) {
            $directory = str_replace(Yii::$app->basePath . '/', '', $defaultConfig->getDirectory());
            if ($this->interactive) {
                $directory = $this->prompt(
                    Yii::t('yii-cycle', 'Specify the path to migration directory.'),
                    ['default' => $directory]
                );
            }
        }
        $directory = Yii::$app->basePath . '/' . $directory;
        if ($this->interactive) {
            $namespace = $this->prompt(
                Yii::t('yii-cycle', 'Specify the migration namespace.'),
                ['default' => $defaultConfig->getNamespace()]
            );
        } else {
            $namespace = $defaultConfig->getNamespace();
        }

        $config = new MigrationConfig([
            'directories' => [$directory],
            'namespace' => $namespace,
            'table' => $defaultConfig->getTable()
        ]);
        $fileRepository = new FileRepository($config);
        $fileName = str_replace('.php', '', basename($fileRepository->createFilename($name)));
        $class = new ClassDeclaration($fileName, 'Migration');
        $class->method('up')->setPublic()->setReturn('void');
        $class->method('down')->setPublic()->setReturn('void');


        $file = new FileDeclaration($namespace);
        $file->addUse(Migration::class);
        $file->addElement($class);

        $fileRepository = new FileRepository($config);
        $fileRepository->registerMigration($name, $fileName, $file->render());
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