<?php
declare(strict_types=1);


namespace Test\Fake;

use Api\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Fake ResponseFormatter
 */
class Formatter implements ResponseFormatter
{

    /**
     * @inheritDoc
     */
    public function formatSuccess(Response $response, $data = null): Response
    {
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function formatRedirect(Response $response, string $url, $data = null): Response
    {
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function formatDirectError(Response $response, int $code, string $message, $detail = null): Response
    {
        return $response;
    }
}
