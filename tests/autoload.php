<?php

declare(strict_types=1);

use yii\web\Application;

define('YII_DEBUG', true);
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config.php';

try {
    (new Application($config));
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}
