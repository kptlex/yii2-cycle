<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Factory;

use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Lex\Yii2\Cycle\Command\MigrateCommand;
use Lex\Yii2\Cycle\Debug\Logger\CycleLogger;
use Lex\Yii2\Cycle\Debug\Panel;
use Lex\Yii2\Cycle\Provider\ProviderInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Database\DatabaseProviderInterface;
use Spiral\Migrations\Migrator;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\debug\Module;
use yii\di\Container;
use yii\di\NotInstantiableException;
use yii\i18n\PhpMessageSource;

use function defined;

final class OrmFactory implements BootstrapInterface
{
    private Container $container;

    public function __construct()
    {
        $this->container = Yii::$container;
    }

    /**
     * @param $app
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function bootstrap($app): void
    {
        Yii::$app->getI18n()->translations['yii-cycle'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/kptlex/yii-cycle/resources/i18n',
        ];

        $this->__invoke();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __invoke()
    {
        Yii::$container->setSingleton(CycleLogger::class);

        $dbal = $this->getDbal();

        $container = $this->container;
        $container->setSingletons(
            [
                FactoryInterface::class => static function () use ($dbal, $container) {
                    $provider = $container->get(ProviderInterface::class);
                    return $provider->getFactory($dbal);
                },
                SchemaInterface::class => static function () use ($container) {
                    $provider = $container->get(ProviderInterface::class);
                    return $provider->getSchema();
                },
                ORMInterface::class => [
                    'class' => ORM::class,
                ],
                ORM::class => [
                    'class' => ORM::class
                ],
                Migrator::class => static function () use ($container, $dbal) {
                    $provider = $container->get(ProviderInterface::class);
                    $migrationFactory = $container->get(MigrationFactory::class);
                    $migrationConfig = $provider->getMigrationConfig();
                    return $migrationFactory->getMigrator($dbal, $migrationConfig);
                }
            ]
        );
        if (Yii::$app->request->isConsoleRequest) {
            Yii::$app->controllerMap['migrator'] = MigrateCommand::class;
        }
    }

    /**
     * @return DatabaseManager
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function getDbal(): DatabaseManager
    {
        if (!Yii::$container->has(DatabaseProviderInterface::class)) {
            Yii::$container->setSingleton(CycleLogger::class);
            $provider = $this->container->get(ProviderInterface::class);
            $dbal = $provider->getDbal();
            $dbal->setLogger(Yii::$container->get(CycleLogger::class));
            Yii::$container->set(DatabaseManager::class, $dbal);
            Yii::$container->set(DatabaseProviderInterface::class, $dbal);
            $this->debug();
        }
        return Yii::$container->get(DatabaseManager::class);
    }

    /**
     * @throws InvalidConfigException
     */
    protected function debug(): void
    {
        $isEnable = (defined('YII_DEBUG') && YII_DEBUG);
        if ($isEnable) {
            /**
             * @var Module $debug
             */
            foreach (Yii::$app->modules as $key => $module) {
                if (is_array($module) && $module['class'] === Module::class) {
                    if (!isset($module['panels'])) {
                        $module['panels'] = [];
                    }
                    $module['panels']['yii-cycle'] = [
                        'class' => Panel::class
                    ];
                    Yii::$app->setModule($key, $module);
                }
                if ($module instanceof Module) {
                    $module->panels['yii-cycle'] = Yii::createObject(
                        Panel::class,
                        [
                            'id' => 'cycle'
                        ]
                    );
                }
            }
        }
    }
}