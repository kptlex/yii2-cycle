<?php

declare(strict_types=1);

use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Lex\Yii\Cycle\Provider\CacheProvider;
use Lex\Yii\Cycle\Provider\ProviderInterface;
use yii\caching\CacheInterface;
use yii\caching\FileCache;
use yii\debug\Module;

return [
    'id' => 'yii-cycle-test',
    'basePath' => __DIR__,
    'aliases' => [
        '@web/assets' => __DIR__,
        '@webroot/assets' => __DIR__ . DIRECTORY_SEPARATOR . '.phpunit.cache/yii/assets'
    ],
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'components' => [
        'cache' => [
            'class' => FileCache::class
        ]
    ],
    'container' => [
        'singletons' => [
            CacheInterface::class => [
                'class' => FileCache::class
            ],
            ProviderInterface::class => [
                'class' => CacheProvider::class,
                'dbal' => [
                    'databases' => [
                        'default' => ['connection' => 'sqlite'],
                    ],
                    'connections' => [
                        'sqlite' => [
                            'driver' => SQLiteDriver::class,
                            'connection' => 'sqlite::memory:',
                            'user' => 'sqlite'
                        ],
                    ]
                ],
                'migrations' => [
                    __DIR__ . DIRECTORY_SEPARATOR . 'Migration'
                ],
                'entities' => [
                    __DIR__ . DIRECTORY_SEPARATOR . 'Entity'
                ],
                'migrationTable' => 'migrations',
                'migrationPath' => __DIR__ . DIRECTORY_SEPARATOR . 'Migration',
                'migrationNamespace' => 'Lex\\Yii\\Cycle\\Tests\\Migration'
            ]
        ]
    ],
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => Module::class,
            'allowedIPs' => ['*']
        ]
    ]
];