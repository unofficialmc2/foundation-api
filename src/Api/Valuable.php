<?php
declare(strict_types=1);

namespace Api;

/**
 * Trait Valuable
 * gestion de la propriété 'value'
 * @package Api
 */
trait Valuable
{
    /** @var mixed */
    private $value;
    private bool $isInit = false;

    /**
     * retourne 'value'
     * @return mixed
     */
    public function value()
    {
        if (!$this->isInit) {
            throw new \RuntimeException("La valeur de " . get_class($this) . " n'est pas initialisée");
        }
        return $this->value;
    }

    /**
     * initialise 'value'
     * @param mixed $value
     */
    protected function setValue($value = null): void
    {
        $this->isInit = true;
        $this->value = $value;
    }

    /**
     * RAZ de 'value'
     */
    protected function clearValue(): void
    {
        $this->isInit = false;
        $this->value = null;
    }
}
