<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Factory;

use Yii;
use Lex\Yii\Cycle\FileRepository;
use Lex\Yii\Cycle\MigrationConfig;
use Lex\Yii\Cycle\Provider\ProviderInterface;
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
        $cycleConfig = new \Spiral\Migrations\Config\MigrationConfig([
            'directory' => $config->getDirectories()[0]
        ]);
        return new Migrator($cycleConfig, $dbal, new FileRepository($config));
    }
}