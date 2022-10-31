<?php
declare(strict_types=1);

namespace Api\Helper;

use RuntimeException;

/**
 * @param  class-string $controller Nom de la class du controller
 * @param  string       $methode    Nom de la méthode du controller
 * @return string                   chaine construite
 */
function cm(string $controller, string $methode): string
{
    if (empty($controller)) {
        throw new RuntimeException("La class du controller n'est pas correctement désigné!");
    }
    if (empty($methode)) {
        throw new RuntimeException("La méthode du controller n'est pas correctement désignée!");
    }
    return $controller . ":" . $methode;
}
