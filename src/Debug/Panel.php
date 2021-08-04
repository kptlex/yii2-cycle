<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Debug;

use Yii;
use Lex\Yii\Cycle\Debug\Logger\CycleLogger;
use Lex\Yii\Cycle\Debug\Search\LogSearch;
use yii\base\InvalidConfigException;
use yii\debug\Panel as DebugPanel;
use yii\di\NotInstantiableException;

use function dirname;

final class Panel extends DebugPanel
{
    public function getDetail(): string
    {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search($this->data['queries']);

        $viewPath = dirname(__DIR__, 2) . '/resources/views/debug/cycle.php';
        return Yii::$app->view->renderFile($viewPath, compact('dataProvider'));
    }

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function save(): array
    {
        $logger = Yii::$container->get(CycleLogger::class);
        return [
            'queries' => $logger->getQueries()
        ];
    }

    public function getName(): string
    {
        return 'Cycle ORM';
    }

    public function getTag(): string
    {
        return 'cycle';
    }
}