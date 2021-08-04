<?php

declare(strict_types=1);

use yii\base\View;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use Lex\Yii\Cycle\Debug\Search\LogSearch;

/** @var View $this */
/** @var ArrayDataProvider $dataProvider */
/** @var LogSearch $searchModel */

try {
    echo GridView::widget(
        [
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'time',
                    'value' => static function ($model) {
                        return date('d.m.Y H:i:s:u', $model['time']);
                    }
                ],
                'query',
                'elapsed',
                'rowCount',
                'executions'
            ]
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}