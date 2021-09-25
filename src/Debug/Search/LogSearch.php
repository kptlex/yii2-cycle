<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Debug\Search;

use yii\data\ArrayDataProvider;

/**
 * Class LogSearch
 * @package Lex\Yii2\Cycle\Debug\Search
 */
final class LogSearch
{
    public ?string $query;

    public ?float $elapsed;

    public ?int $rowCount;

    public ?int $executions;

    public function __construct()
    {
        $this->query = null;
        $this->elapsed = null;
        $this->rowCount = null;
        $this->executions = null;
    }


    public function search(array $models): ArrayDataProvider
    {
        $queries = [];
        foreach ($models as $model) {
            if (!isset($queries[$model['query']])) {
                $queries[$model['query']] = 0;
            }
            $queries[$model['query']]++;
        }
        foreach ($models as &$model) {
            $model['executions'] = $queries[$model['query']];
        }
        return new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'query',
                    'elapsed',
                    'rowCount',
                    'time',
                    'executions'
                ],
                'defaultOrder' => [
                    'time' => SORT_ASC
                ]
            ]
        ]);
    }
}