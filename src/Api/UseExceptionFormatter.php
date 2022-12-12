<?php
declare(strict_types=1);

namespace FoundationApi;

use Throwable;

/**
 * Trait pour fournir une méthode de mise en forme des exceptions
 */
trait UseExceptionFormatter
{
    /**
     * Mise en forme d'une exception
     * @param Throwable $err
     * @param bool $withTrace
     * @return string
     */
    protected static function exceptionToString(Throwable $err, bool $withTrace): string
    {
        if ($withTrace) {
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
        return sprintf(
            "Exception : %s (code %d)\nFichier : %s(%d)\nMessage : %s",
            get_class($err),
            $err->getCode(),
            $err->getFile(),
            $err->getLine(),
            $err->getMessage()
        );
    }
}
