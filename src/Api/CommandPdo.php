<?php
declare(strict_types=1);

namespace Api;

use PDO;
use Psr\Log\LoggerInterface;

/**
 * Class CommandPdo
 * @package Api
 */
abstract class CommandPdo extends Command
{
    use PdoQueryable;

    /**
     * CommandPdo constructor.
     * @param PDO $pdo
     * @param LoggerInterface $logger
     */
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->setPdo($pdo);
    }
}
