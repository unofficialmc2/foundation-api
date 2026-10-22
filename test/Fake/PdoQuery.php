<?php
declare(strict_types=1);

namespace Test\Fake;

use FoundationApi\PdoQueryable;
use PDO;
use PDOStatement;

/**
 * Fake requête PDO : expose les méthodes protégées de PdoQueryable
 */
class PdoQuery
{
    use PdoQueryable;

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->setPdo($pdo);
    }

    /**
     * @param string $reqSql
     * @return PDOStatement
     */
    public function statement(string $reqSql): PDOStatement
    {
        $this->setReqSql($reqSql);
        return $this->execute([]);
    }
}
