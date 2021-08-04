<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Factory;

use Yii;
use Lex\Yii\Cycle\FileRepository;
use Lex\Yii\Cycle\Provider\ProviderInterface;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use Spiral\Migrations\Config\MigrationConfig;
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
        return new Migrator($config, $dbal, new FileRepository($config));
    }
}