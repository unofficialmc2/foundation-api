<?php
declare(strict_types=1);

namespace Test\Fake;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Routing\RouteContext;

/**
 * Middleware de Test
 */
class Middleware extends \Api\Middleware
{

    const MIDDLEWARE = "middleware";

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $request = $request->withAttribute(self::MIDDLEWARE, true);
        return $handler->handle($request);
    }
}
