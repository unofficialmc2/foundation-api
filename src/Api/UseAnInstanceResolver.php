<?php
declare(strict_types=1);

namespace Api;

use InstanceResolver\ResolverClass;
use RuntimeException;
use Throwable;
use UnderflowException;

/**
 * permet d'utiliser la méthode 'resolve'
 */
trait UseAnInstanceResolver
{

    private ?ResolverClass $autowiring = null;


    /**
     * @param string $needle
     * @return mixed
     */
    final protected function resolve(string $needle)
    {
        if (!isset($this->container)) {
            throw new UnderflowException("Impossible d'utiliser le trait 'UseAnInstanceResolver', il n'y a pas de"
                . " 'container' accessible");
        }
        if (!$this->container->has(ResolverClass::class)) {
            throw new UnderflowException("Impossible d'utiliser le trait 'UseAnInstanceResolver', le 'container' "
                . "ne fourni pas de 'ResolverClass'");
        }
        try {
            if ($this->autowiring === null) {
                $this->autowiring = $this->container->get(ResolverClass::class);
            }
            /** @var ResolverClass $autowiring */
            $autowiring = $this->autowiring;
            return $autowiring($needle);
        } catch (Throwable $e) {
            throw new RuntimeException("Impossible de résoudre " . $needle, $e->getCode(), $e);
        }
    }
}
