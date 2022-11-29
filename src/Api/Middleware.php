<?php
declare(strict_types=1);

namespace FoundationApi;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class Middleware
 * @package Api
 */
abstract class Middleware
{
    use UseAnInstanceResolver;
    use UseALogger;
    use UseExceptionFormatter;

    /**
     * Middleware constructor.
     * @param ContainerInterface $container
     */
    final public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Fonction du middleware
     * @param  Request        $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     * @return Response                PSR-7 response
     */
    abstract public function __invoke(Request $request, RequestHandler $handler): Response;
}
