<?php

namespace Lex\Yii2\Cycle\Tests;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\grid\GridView;
use Lex\Yii2\Cycle\Debug\Logger\CycleLogger;
use Lex\Yii2\Cycle\Debug\Panel;

final class LoggerTest extends TestCase
{
    private function getPanel()
    {
        $panel = Yii::createObject(Panel::class, [
            'id' => 'cycle'
        ]);
        $panel->init();
        return $panel;
    }

    public function testCreatePanel(): Panel
    {
        $panel = $this->getPanel();
        self::assertInstanceOf(Panel::class, $panel);
        self::assertNotEmpty($panel->getName());
        self::assertNotEmpty($panel->getTag());
        self::assertTrue($panel->isEnabled());
        return $panel;
    }

    /**
     * @depends testCreatePanel
     */
    public function testDebug(Panel $panel)
    {
        $data = $panel->save();
        $panel->data = $data;
        self::assertNotEmpty($data);
        $panel->getDetail();
        Yii::$container->setDefinitions([
            GridView::class => [
                '__class' => Data\Yii\GridView::class
            ]
        ]);
        $detail = $panel->getDetail();
        self::assertNotEmpty($detail);
        $logger = Yii::$container->get(CycleLogger::class);
        foreach (['info', 'emergency', 'alert', 'notice', 'debug', 'warning', 'error', 'critical'] as $method) {
            $logger->$method('test', []);
        }
        $logger->log(0, 'test', []);
    }
}