# Yii Cycle

Cycle ORM adapter for Yii2 framework with debug panel.

[![Latest Stable Version](https://poser.pugx.org/kptlex/yii2-cycle/v)](//packagist.org/packages/kptlex/yii2-cycle)
[![Latest Unstable Version](https://poser.pugx.org/kptlex/yii2-cycle/v/unstable)](//packagist.org/packages/kptlex/yii2-cycle)
[![Build Status](https://github.com/kptlex/yii2-cycle/workflows/build/badge.svg)](https://github.com/kptlex/yii2-cycle/actions)
[![codecov](https://codecov.io/gh/kptlex/yii2-cycle/branch/main/graph/badge.svg?token=S5C6K9Y760)](https://codecov.io/gh/kptlex/yii2-cycle)

# Installation

1. Use composer.

```
composer require kptlex/yii2-cycle
```

2. Added configuration to your configs:

```php

use Spiral\Database\Driver\MySQL\MySQLDriver;
use Lex\Yii2\Cycle\Provider\CacheProvider;
use Lex\Yii2\Cycle\Factory\OrmFactory;
use Lex\Yii2\Cycle\Provider\ProviderInterface;
/**
* Example mysql configuration
 */
$_dbal = [
    'default' => 'mysql',
    'databases' => [
        'mysql' => [
            'connection' => 'mysql'
        ]
    ],
    'connections' => [
        'mysql' => [
            'driver' => MySQLDriver::class,
            'options' => [
                'connection' => 'mysql:host=127.0.0.1;dbname=dbname',
                'username' => 'username',
                'password' => 'password',
                'timezone' => 'Europe/Moscow'
            ]
        ]
    ]
];

/**
* Example adapter configuration
 */
$config = [
    'bootstrap' => [OrmFactory::class],
    'container' => [
        'definitions' => [
            ProviderInterface::class => [
                'class' => CacheProvider::class,
                'dbal' => $_dbal,
                'entities' => [
                    '@App/Entity' //namespace for your entities
                ],
                'migrations' => [
                    '@App/Migration' //path or alias to your migrations
                ],
                'migrationPath' => '@app/src/Migration', //default path or alias for new migrations
//                'migrationTable' => 'migration', //default - migration
                'migrationNamespace' => 'App\\Migration' //namespace for new migrations
            ]
        ]
    ]
];
```

# Migrations

Use the following commands to manage migrations

* Apply migrations

```
php yii migrator
```

* Create migrations

```
php yii migrator/create <migrationName>
```

* Cancel migrations

```
php yii migrator/down
```

# More

1. [Spiral database configuration (DBAL)](https://spiral.dev/docs/database-configuration#configuration)
2. [Cycle ORM Docs](https://cycle-orm.dev/docs)
