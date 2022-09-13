<?php
declare(strict_types=1);

namespace Api;

use Slim\Http\Response;

/**
 * Interface pour les class de mise en forme de réponse
 */
interface ResponseFormatter
{

    /**
     * Formate une réponse avec succes avec un objet $data
     * @param \Slim\Http\Response $response
     * @param object|array<mixed>|null $data (null par defaut)
     * @return \Slim\Http\Response
     */
    public function formatSuccess(Response $response, $data = null): Response;

    /**
     * Formate une réponse redirect avec un objet $data
     * @param \Slim\Http\Response $response
     * @param string $url
     * @param object|array<mixed>|null $data (null par defaut)
     * @return Response
     */
    public function formatRedirect(Response $response, string $url, $data = null): Response;

    /**
     * Formate une réponse avec erreur en spécifiant tout les elements
     * @param \Slim\Http\Response $response
     * @param integer $code
     * @param string $message
     * @param object|array<mixed>|null $detail
     * @return Response
     */
    public function formatDirectError(Response $response, int $code, string $message, $detail = null): Response;
}
