<?php

namespace Api;

use FoundationApi\Factory;
use HttpException\NotFoundException;
use Test\TestCase;
use RuntimeException;
use Slim\Psr7\Factory\ServerRequestFactory;
use Test\Fake\Formatter;

/**
 *
 */
class HandlerErrorMiddlewareTest extends TestCase
{
    /**
     * test RuntimeException
     * @return void
     */
    public function testRuntimeException(): void
    {
        $app = Factory::create([
            'logger' => ['path' => __DIR__ . "/../log", 'name' => 'test'],
            'ResponseFormatterClass' => Formatter::class
        ]);
        $app->get('/', function () {
            throw new RuntimeException("test");
        });
        $requestFactory = new ServerRequestFactory();
        $response = $app->handle($requestFactory->createServerRequest('GET', 'http://localhost'));
        self::assertEquals(500, $response->getStatusCode());
    }

    /**
     * test l'Exception Slim NotFound
     * @return void
     */
    public function testNotFoundException(): void
    {
        $app = Factory::create([
            'logger' => ['path' => __DIR__ . "/../log", 'name' => 'test'],
            'ResponseFormatterClass' => Formatter::class
        ]);
        $app->get('/maison', function () {
            throw new RuntimeException("test");
        });
        $requestFactory = new ServerRequestFactory();
        $response = $app->handle($requestFactory->createServerRequest('GET', 'http://localhost'));
        self::assertEquals(404, $response->getStatusCode());
    }


    /**
     * test l'Exception Slim NotFound
     * @return void
     */
    public function testNotAllowedException(): void
    {
        $app = Factory::create([
            'logger' => ['path' => __DIR__ . "/../log", 'name' => 'test'],
            'ResponseFormatterClass' => Formatter::class
        ]);
        $app->post('/', function () {
            throw new RuntimeException("test");
        });
        $requestFactory = new ServerRequestFactory();
        $response = $app->handle($requestFactory->createServerRequest('GET', 'http://localhost'));
        self::assertEquals(405, $response->getStatusCode());
    }

    /**
     * test HttpException
     * @return void
     */
    public function testHttpException(): void
    {
        $app = Factory::create([
            'logger' => ['path' => __DIR__ . "/../log", 'name' => 'test'],
            'ResponseFormatterClass' => Formatter::class
        ]);
        $app->get('/', function () {
            throw new NotFoundException("test");
        });
        $requestFactory = new ServerRequestFactory();
        $response = $app->handle($requestFactory->createServerRequest('GET', 'http://localhost'));
        self::assertEquals(404, $response->getStatusCode());
    }
}
