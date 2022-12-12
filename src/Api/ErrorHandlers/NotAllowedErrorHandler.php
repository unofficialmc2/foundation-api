<?php
declare(strict_types=1);

namespace FoundationApi\ErrorHandlers;

use FoundationApi\ErrorHandler;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

/**
 * Class gérant le retour des Exceptions NotAllowedException de Slim
 */
class NotAllowedErrorHandler extends ErrorHandler
{


    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable $exception
     * @param bool $displayErrorDetails unused // false
     * @param bool $logErrors unused // true
     * @param bool $logErrorDetails unused // true
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
    {
        if (!is_a($exception, HttpMethodNotAllowedException::class)) {
            throw new LogicException(
                "Appel de " . __METHOD__ . " pour une exception != de " . HttpMethodNotAllowedException::class
                . " mais " . get_class($exception)
            );
        }
        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);
        $response = (new ResponseFactory())->createResponse();
        $logger->warning("Méthode non autorisée : ({$request->getMethod()}) {$request->getUri()}");
        $this->logException($logger, $exception);
        $this->logRequest($logger, $request);
        return $this->formatResponse(
            $response,
            $exception->getCode(),
            $exception->getMessage()
        );
    }
}
