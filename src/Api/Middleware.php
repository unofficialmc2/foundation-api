<?php
declare(strict_types=1);

namespace Api;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Container;
use Slim\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Middleware
 * @package Api
 */
abstract class Middleware
{
    use UseAnInstanceResolver;
    use UseALogger;
    use UseExceptionFormatter;

    protected Container $container;

    /**
     * Middleware constructor.
     * @param Container $container
     */
    final public function __construct(Container $container)
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
