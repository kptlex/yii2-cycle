<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Provider;

use Cycle\ORM\SchemaInterface;
use yii\caching\CacheInterface;
use yii\caching\ChainedDependency;

class CacheProvider extends FileProvider
{
    public const DEFAULT_KEY = 'Cycle-ORM-Schema';

    private CacheInterface $cache;

    public string $cacheKey;

    public function __construct(CacheInterface $cache)
    {
        $this->cacheKey = self::DEFAULT_KEY;
        $this->cache = $cache;
        parent::__construct();
    }

    public function getSchema(): SchemaInterface
    {
        return $this->cache->getOrSet($this->cacheKey, function () {
            return parent::getSchema();
        });
    }
}