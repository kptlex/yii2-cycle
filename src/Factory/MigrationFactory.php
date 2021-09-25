<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Factory;

use Spiral\Migrations\Config\MigrationConfig as SpiralMigrationConfig;
use Yii;
use Lex\Yii2\Cycle\FileRepository;
use Lex\Yii2\Cycle\MigrationConfig;
use Lex\Yii2\Cycle\Provider\ProviderInterface;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Migrator;

final class MigrationFactory
{
    private ProviderInterface $provider;

    /**
     * MigrationFactory constructor.
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct()
    {
        $this->provider = Yii::$container->get(ProviderInterface::class);
    }

    public function getMigrator(DatabaseManager $dbal, ?MigrationConfig $config = null): Migrator
    {
        if ($config === null) {
            $config = $this->provider->getMigrationConfig();
        }
        $cycleConfig = new SpiralMigrationConfig([
            'directory' => $config->getDirectories()[0],
            'namespace' => $config->getNamespace(),
            'table' => $config->getTable()
        ]);
        return new Migrator($cycleConfig, $dbal, new FileRepository($config));
    }
}