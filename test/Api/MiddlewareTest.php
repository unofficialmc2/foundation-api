<?php
declare(strict_types=1);

namespace Api;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ServerRequestFactory;
use Test\Fake\Middleware as MiddlewareAlias;

/**
 * Test de la class Middleware
 */
class MiddlewareTest extends TestCase
{
    public static function testAddMiddleware(): void
    {
        $app = Factory::create([
            "logger"=>["name"=>"test","path" => __DIR__ . "/../log"]
        ]);
        $app->add(MiddlewareAlias::class);
        $assert = self::assertTrue(...);
        $app->get('/', function (Request $request, $response) use ($assert) {
            $assert($request->getAttribute(MiddlewareAlias::MIDDLEWARE));
            return $response;
        })->setName("root");
        $app->addRoutingMiddleware();
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/');
        $response = $app->handle($request);
    }
}
