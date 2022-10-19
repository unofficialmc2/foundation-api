<?php
declare(strict_types=1);

namespace Api;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Middleware
 * @package Api
 */
abstract class Middleware
{
    use UseAnInstanceResolver;
    use UseALogger;
    use UseExceptionFormatter;

    protected ContainerInterface $container;

    /**
     * Middleware constructor.
     * @param ContainerInterface $container
     */
    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @phpstan-param callable(Request $request, Response $response): Response $next
     * @return Response
     */
    abstract public function __invoke(Request $request, Response $response, callable $next): Response;
}
