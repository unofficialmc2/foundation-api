<?php
declare(strict_types=1);

namespace FoundationApi;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

/**
 * Class de base pour les ErrorHandler
 */
abstract class ErrorHandler implements ErrorHandlerInterface
{

    /**
     * @param \FoundationApi\Container $container
     */
    public function __construct(protected Container $container)
    {
    }

    /**
     * Methode principal du handler
     */
    abstract public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface;

    /**
     * @param string $message
     * @return string
     */
    protected function utf8Encode(string $message): string
    {
        if (!str_contains($message, "\\u")) {
            return utf8_encode($message);
        }
        return $message;
    }

    /**
     * log une requête
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    protected function logRequest(LoggerInterface $logger, Request $request): void
    {
        $logger->notice($this->getRequestBasicsFormatted($request));
        $logger->debug($this->getRequestHeaderFormatted($request));
        $body = (string)$request->getBody();
        if (str_contains($body, "password")) {
            $body = $this->replacePassWord($logger, $body);
        }
        if (!empty($body)) {
            $logger->debug($body);
        }
    }

    /**
     * Fonction qui remplace les mots de passe en clair part des étoiles
     * @param LoggerInterface $logger
     * @param string $message
     * @return string
     */
    final protected function replacePassWord(LoggerInterface $logger, string $message): string
    {
        try {
            $jsonMessage = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            $jsonMessage["password"]= "***********";
            return json_encode($jsonMessage, JSON_THROW_ON_ERROR);
        } catch (\JsonException $_) {
            $logger->critical("Le mot de passe n'a pas pu être encodée");
            return $message;
        }
    }

    /**
     * Format les éléments de base d'une requête
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    private function getRequestBasicsFormatted(Request $request): string
    {
        $protocol = $request->getProtocolVersion();
        $method = (string)$request->getMethod();
        $url = (string)$request->getUri();
        return "HTTP/$protocol $method $url";
    }

    /**
     * Format l'entête d'une requête
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    private function getRequestHeaderFormatted(Request $request): string
    {
        $headers = $request->getHeaders();
        $strHeaders = '';
        foreach ($headers as $key => $values) {
            $strHeaders .= PHP_EOL . $key . ': ' . implode(';', $values);
        }
        return trim($strHeaders);
    }

    /**
     * @param Response $response
     * @param int $status
     * @param string $message
     * @param null|object|mixed[] $detail
     * @return Response
     */
    protected function formatResponse(Response $response, int $status, string $message, $detail = null): Response
    {
        $settings = $this->container->get('settings');
        $logger = $this->container->get(LoggerInterface::class);
        if (!isset($settings['ResponseFormatterClass'])) {
            throw new RuntimeException(
                "le nom d'une class ResponseFormatterInterface n'est pas initialisé "
                . "dans la config (response-formatter/class)"
            );
        }
        try {
            /** @var class-string<ResponseFormatterInterface> $responseFormatterClass */
            $responseFormatterClass = $settings['ResponseFormatterClass'];
            $formatter = new $responseFormatterClass();
            return $formatter->formatDirectError($response, $status, $message, $detail)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'POST, GET, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-AUTHTOKEN');
        } catch (ContainerExceptionInterface $e) {
            $this->logException($logger, $e);
            throw new RuntimeException("Impossible d'initialiser le ResponseFormatter");
        }
    }

    /**
     * Log une exception
     * @param LoggerInterface $logger
     * @param Throwable $err
     */
    protected function logException(LoggerInterface $logger, Throwable $err): void
    {
        $message = sprintf(
            "Exception : %s (code %d)\nFichier : %s(%d)\nMessage : %s",
            get_class($err),
            $err->getCode(),
            $err->getFile(),
            $err->getLine(),
            $err->getMessage()
        );
        $details = [
            'trace' => $err->getTrace()
        ];
        $logger->debug($message, $details);
    }
}
