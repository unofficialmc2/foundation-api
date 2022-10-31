<?php
declare(strict_types=1);

namespace Api;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Test\Fake\Controller as FakeController;
use Test\Fake\Formatter;

use Test\Fake\Middleware as MiddlewareAlias;
use function Api\Helper\cm;

/**
 * Test de la class Controller
 */
class ControllerTest extends TestCase
{
    public function testController(): void
    {
        $assert = self::assertTrue(...);
        $container = new Container();
        $container->set(ResponseFormatter::class, function (ContainerInterface $c) {
            return new Formatter();
        });
        $ctrl = new FakeController($container);
        $ctrl->setCustomAction(function () use ($assert) {
            $assert(true);
        });
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/');
        $factory = new ResponseFactory();
        $response = $factory->createResponse(200);
        $ctrl->index($request, $response, []);
    }

    public function testSetControllerMethodeToRoute(): void
    {
        $app = Factory::create([
            "logger"=>["name"=>"test","path" => __DIR__ . "/../log"]
        ]);
        /** @var \Api\Container $container */
        $container = $app->getContainer();
        $container->set(ResponseFormatter::class, function (ContainerInterface $c) {
            return new Formatter();
        });
        $app->get('/', cm(FakeController::class, 'index'));
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/');
        $response = $app->handle($request);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }
}
