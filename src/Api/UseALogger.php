<?php
declare(strict_types=1);

namespace FoundationApi;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use UnderflowException;

/**
 * Utilisation d'un locgger dans une class
 */
trait UseALogger
{

    protected ContainerInterface $container;
    private ?LoggerInterface $logger = null;

    /**
     * @return LoggerInterface
     */
    final protected function log(): LoggerInterface
    {
        try {
            if ($this->logger === null) {
                if (!isset($this->container)) {
                    throw new UnderflowException("Impossible d'utiliser le trait 'UseALogger', il n'y a pas de"
                        . " 'container' accessible");
                }
                if (!$this->container->has(LoggerInterface::class)) {
                    throw new UnderflowException("Impossible d'utiliser le trait 'UseALogger', le 'container' ne fourni"
                        . " pas de 'LoggerInterface'");
                }
                /** @var LoggerInterface $logger */
                $logger = $this->container->get(LoggerInterface::class);
                $this->logger = $logger;
            }
            return $this->logger;
        } catch (ContainerExceptionInterface $e) {
            throw new \RuntimeException("Impossible de trouver le logger", $e->getCode(), $e);
        }
    }
}
