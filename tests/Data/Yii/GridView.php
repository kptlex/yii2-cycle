<?php

namespace Lex\Yii2\Cycle\Tests\Data\Yii;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class GridView extends \yii\grid\GridView
{
    public function run()
    {
        return $this->renderTableBody();
    }
}