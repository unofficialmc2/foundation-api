<?php
declare(strict_types=1);

namespace Api;

use DateTime;
use HttpException\HttpException;
use InstanceResolver\ResolverClass;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Class permettant de créer une app Slim v4
 */
class Factory
{
    /**
     * @param array<string,mixed> $config
     * @return \Slim\App
     */
    public static function create(array $config): App
    {
        $container = self::getContainer($config);
        $instanceResolver = self::getInstanceResolver($container);
        $logger = self::getLogger($config);
        $responseFactory = self::getResponseFactory();

        $app = new \Slim\App(
            $responseFactory,
            $container
        );
        $errMiddleware = $app->addErrorMiddleware(true, true, true);
        $errMiddleware->setErrorHandler(HttpException::class, static function () {
        });

        return $app;
    }

    /**
     * @param array<string,mixed> $config
     * @return \Psr\Container\ContainerInterface
     */
    protected static function getContainer(array $config): ContainerInterface
    {
        return new \Pimple\Psr11\Container(
            new \Pimple\Container(
                $config
            )
        );
    }

    /**
     * @param array<string,mixed> $config
     * @return \Psr\Log\LoggerInterface
     */
    protected static function getLogger(array $config): LoggerInterface
    {
        $settings = $config['settings'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipClient = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipClient = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipClient = $_SERVER['REMOTE_ADDR']??'unknown';
        }
        $logger = new Logger($settings['logger']['name']);
        $logPath = $settings['logger']['path'];
        $logError = rtrim($logPath, '/') . '/events-' . (new DateTime())->format('Y-m-d') . '.log';
        $logDebug = rtrim($logPath, '/') . '/events_json.log';
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(static function (LogRecord $record) use ($ipClient): LogRecord {
            $record['extra']['info'] = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '?',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '?',
                'IP' => $ipClient,
                'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '?'
            ];
            return $record;
        });
        $steam = new StreamHandler($logError, Level::Warning);
        $jsonSteam = new RotatingFileHandler($logDebug, 15, Level::Debug);
        $jsonSteam->setFormatter(new JsonFormatter());
        $logger->pushHandler($steam);
        $logger->pushHandler($jsonSteam);
        return $logger;
    }

    /**
     * Récupération d'une factory de réponse
     * @return \Psr\Http\Message\ResponseFactoryInterface
     */
    protected static function getResponseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }

    /**
     * Récupération d'un resolver d'instance
     * @param \Psr\Container\ContainerInterface $container
     * @return \InstanceResolver\ResolverClass
     */
    protected static function getInstanceResolver(ContainerInterface $container): ResolverClass
    {
        return new ResolverClass($container);
    }
}
