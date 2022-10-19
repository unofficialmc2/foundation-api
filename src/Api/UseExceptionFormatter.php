<?php
declare(strict_types=1);

namespace Api;

/**
 * Trait pour fournir une méthode de mise en forme des exceptions
 */
trait UseExceptionFormatter
{
    /**
     * Mise en forme d'une exception
     * @param \Throwable $err
     * @return string
     */
    protected static function exceptionToString(\Throwable $err): string
    {
        return sprintf(
            "Exception : %s (code %d)\nFichier : %s(%d)\nMessage : %s\n%s",
            get_class($err),
            $err->getCode(),
            $err->getFile(),
            $err->getLine(),
            $err->getMessage(),
            $err->getTraceAsString()
        );
    }
}
