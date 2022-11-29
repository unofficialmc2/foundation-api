<?php
declare(strict_types=1);

namespace FoundationApi;

use Psr\Log\LoggerInterface;

/**
 * Class Command
 * @package Api
 */
abstract class Command
{
    use Valuable;
    use UseALogger;

    /**
     * Command constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execution de la command
     */
    abstract public function __invoke(): void;
}
