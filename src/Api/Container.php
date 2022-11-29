<?php
declare(strict_types=1);

namespace FoundationApi;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

/**
 * Surcharge un container Pimple pour le rendre compatible avec ContainerInterface
 */
class Container extends PimpleContainer implements ContainerInterface
{

    /**
     * initialise une valeur du container
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set(string $id, mixed $value): void
    {
        $this->offsetSet($id, $value);
    }

    /**
     * getter
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    /**
     * isset ?
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }
}
