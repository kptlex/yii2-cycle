<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Debug\Logger;

use Psr\Log\LoggerInterface;

final class CycleLogger implements LoggerInterface
{
    public const LOG_TAG = 'cycle';

    private array $data;

    /**
     * CycleLogger constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    public function emergency($message, array $context = []): void
    {
        $this->attach('emergency', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->attach('alert', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->attach('critical', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->attach('error', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->attach('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->attach('notice', $message, $context);
    }


    public function info($message, array $context = []): void
    {
        $this->attach('info', $message, $context);

        $context['query'] = $message;
        $context['time'] = time();

        if (!isset($this->data['query'])) {
            $this->data['query'] = [];
        }
        $this->data['query'][] = $context;
    }

    public function getQueries(): array
    {
        return $this->data['query'] ?? [];
    }

    public function debug($message, array $context = []): void
    {
        $this->attach('debug', $message, $context);
    }

    private function attach($category, $message, array $context): void
    {
        if (!isset($this->data[$category])) {
            $this->data[$category] = [];
        }
        $this->data[$category] = compact('message', 'context');
    }

    public function log($level, $message, array $context = []): void
    {
        $context['level'] = $level;
        $this->attach('log', $message, $context);
    }
}