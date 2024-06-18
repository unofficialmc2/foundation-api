<?php
declare(strict_types=1);

namespace FoundationApi;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class DynamicLogger implements LoggerInterface
{
    use LoggerTrait;

    private $logger;
    private $logPath;
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeLogger($config['logger']['path']);
    }

    private function initializeLogger(string $logPath): void
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipClient = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipClient = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipClient = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        $this->logger = new Logger($this->config['logger']['name']);
        $this->logPath = $logPath;

        $logError = rtrim($logPath, '/') . '/events-' . (new \DateTime())->format('Y-m-d') . '.log';
        $logDebug = rtrim($logPath, '/') . '/events_json.log';

        $this->logger->pushProcessor(new UidProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor(static function ($record) use ($ipClient){
            $record['extra']['info'] = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '?',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '?',
                'IP' => $ipClient,
                'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '?'
            ];
            return $record;
        });

        $stream = new StreamHandler($logError, Logger::WARNING);
        $jsonStream = new RotatingFileHandler($logDebug, 15, Logger::DEBUG);
        $jsonStream->setFormatter(new JsonFormatter());

        $this->logger->pushHandler($stream);
        $this->logger->pushHandler($jsonStream);
    }

    public function updateLogPath(string $newLogPath): void
    {
        $this->initializeLogger($newLogPath);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
