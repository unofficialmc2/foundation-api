<?php
declare(strict_types=1);

namespace FoundationApi;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Interface pour les class de mise en forme de réponse
 */
interface ResponseFormatterInterface
{

    /**
     * Formate une réponse avec succes avec un objet $data
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param mixed $data (null par defaut)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function formatSuccess(Response $response, mixed $data = null): Response;

    /**
     * Formate une réponse redirect avec un objet $data
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string $url
     * @param mixed $data (null par defaut)
     * @return Response
     */
    public function formatRedirect(Response $response, string $url, mixed $data = null): Response;

    /**
     * Formate une réponse avec erreur en spécifiant tout les elements
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param integer $code
     * @param string $message
     * @param mixed $detail
     * @return Response
     */
    public function formatDirectError(Response $response, int $code, string $message, mixed $detail = null): Response;
}
