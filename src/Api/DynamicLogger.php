<?php
declare(strict_types=1);

namespace FoundationApi;

use DateTime;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

/**
 * Logger dont le dossier de destination peut être modifié en cours d'exécution
 */
class DynamicLogger implements LoggerInterface
{
    use LoggerTrait;

    private Logger $logger;
    /** @var array<string,mixed> */
    private array $config;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeLogger($config['logger']['path']);
    }

    /**
     * Initialise le logger Monolog pour le dossier donné
     * @param string $logPath
     * @return void
     */
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

        $logError = rtrim($logPath, '/') . '/events-' . (new DateTime())->format('Y-m-d') . '.log';
        $logDebug = rtrim($logPath, '/') . '/events_json.log';

        $this->logger->pushProcessor(new UidProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor(static function (LogRecord $record) use ($ipClient): LogRecord {
            $record['extra']['info'] = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '?',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '?',
                'IP' => $ipClient,
                'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '?'
            ];
            return $record;
        });

        $stream = new StreamHandler($logError, Level::Warning);
        $jsonStream = new RotatingFileHandler($logDebug, 15, Level::Debug);
        $jsonStream->setFormatter(new JsonFormatter());

        $this->logger->pushHandler($stream);
        $this->logger->pushHandler($jsonStream);
    }

    /**
     * Change le dossier de destination des logs
     * @param string $newLogPath
     * @return void
     */
    public function updateLogPath(string $newLogPath): void
    {
        $this->initializeLogger($newLogPath);
    }

    /**
     * @inheritDoc
     * @param mixed $level
     * @param string|Stringable $message
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
