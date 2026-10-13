<?php
declare(strict_types=1);

namespace Test\Fake;

use FoundationApi\PdoQueryable;
use PDO;

/**
 * Fake requête PDO : expose les méthodes protégées de PdoQueryable
 */
class PdoQuery
{
    use PdoQueryable;

    /**
     * @param PDO $pdo
     * @param string $charset encodage de la base
     */
    public function __construct(PDO $pdo, string $charset = 'UTF-8')
    {
        $this->setPdo($pdo);
        $this->setCharset($charset);
    }

    /**
     * @param string $reqSql
     * @param array<string|int,mixed> $param
     * @return array<string,mixed>|null
     */
    public function one(string $reqSql, array $param = []): ?array
    {
        $this->setReqSql($reqSql);
        return $this->fetchOne($param);
    }
}
