<?php
declare(strict_types=1);

namespace Test\Fake;

use FoundationApi\Controller as ApiController;
use Closure;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Fake Controller pour les tests
 */
class Controller extends ApiController
{
    private Closure|null $action = null;

    /**
     * @param Request $request
     * @param Response $response
     * @param array<string,string> $args
     * @return Response
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        if ($this->action !== null) {
            ($this->action)();
        }
        return $response;
    }

    /**
     * @param callable|Closure $action
     * @return void
     */
    public function setCustomAction(callable|Closure $action): void
    {
        if (is_callable($action)) {
            /**
             * Syntax non simplifiée pour que le sens de cette ligne soit explicite
             * @noinspection PhpClosureCanBeConvertedToFirstClassCallableInspection
             */
            $action = Closure::fromCallable($action);
        }
        $this->action = $action;
    }
}
