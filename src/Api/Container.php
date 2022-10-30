<?php

namespace Api;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

/**
 * Surcharge un container Pimple pour le rendre compatible avec ContainerInterface
 */
class Container implements ContainerInterface
{
    /**
     * Constructeur
     * @param PimpleContainer $container
     */
    public function __construct(private PimpleContainer $container)
    {
    }

    /**
     * initialise une valeur du container
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set(string $id, mixed $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * getter
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->container[$id];
    }

    /**
     * isset ?
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}