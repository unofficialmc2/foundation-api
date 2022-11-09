<?php
declare(strict_types=1);

namespace Api;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Interface pour les class de mise en forme de réponse
 */
interface ResponseFormatterInterface
{

    /**
     * Formate une réponse avec succes avec un objet $data
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param object|array<mixed>|null $data (null par defaut)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function formatSuccess(Response $response, array|object|null $data = null): Response;

    /**
     * Formate une réponse redirect avec un objet $data
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string $url
     * @param object|array<mixed>|null $data (null par defaut)
     * @return Response
     */
    public function formatRedirect(Response $response, string $url, array|object|null $data = null): Response;

    /**
     * Formate une réponse avec erreur en spécifiant tout les elements
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param integer $code
     * @param string $message
     * @param object|array<mixed>|null $detail
     * @return Response
     */
    public function formatDirectError(Response $response, int $code, string $message, array|object|null $detail = null): Response;
}
