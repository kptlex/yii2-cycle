<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Provider;

use Cycle\ORM\FactoryInterface;
use Cycle\ORM\SchemaInterface;
use Spiral\Database\DatabaseManager;
use Lex\Yii\Cycle\MigrationConfig;

interface ProviderInterface
{
    public function getDbal(): DatabaseManager;

    public function getFactory(DatabaseManager $dbal): FactoryInterface;

    public function getSchema(): SchemaInterface;

    public function getMigrationConfig(): MigrationConfig;
}