<?php
declare(strict_types=1);

namespace Api;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Trait PdoQueryable
 * @package Api
 */
trait PdoQueryable
{
    protected ?PDO $pdo = null;
    /** @var array<string,PDOStatement> */
    private array $cache = [];
    private string $charset = 'UTF-8';
    private string $reqSql;

    /**
     * @param PDO $pdo
     */
    protected function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $charset
     */
    protected function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @param string $reqSql
     */
    protected function setReqSql(string $reqSql): void
    {
        $this->reqSql = $reqSql;
    }

    /**
     * Execute la requête SQL
     * @param array<mixed> $params
     * @return PDOStatement
     */
    protected function execute(array $params): PDOStatement
    {
        $stm = $this->prepare();
        if (empty($params)) {
            $ok = $stm->execute();
        } else {
            $params = $this->controlInputEncoding($params);
            $ok = $stm->execute($params);
        }
        if (!$ok) {
            /** @noinspection JsonEncodingApiUsageInspection */
            $paramsJson = json_encode($params);
            throw new RuntimeException(
                'Impossible d\'exécuter la requête : \'' . PHP_EOL .
                $this->reqSql . PHP_EOL .
                'dans ' . static::class . PHP_EOL .
                'avec ' . ($paramsJson ?: 'error...')
            );
        }
        return $stm;
    }

    /**
     * Prépare la requête SQL
     * @return PDOStatement
     */
    private function prepare(): PDOStatement
    {
        if ($this->pdo === null) {
            throw new RuntimeException("PDO n'est pas initialisé");
        }
        if (empty($this->reqSql)) {
            throw new RuntimeException("Il n'y a pas de requêtes initialisée dans " . static::class);
        }
        $reqSql = $this->reqSql;
        $keyReq = md5($reqSql);
        if (array_key_exists($keyReq, $this->cache)) {
            return $this->cache[$keyReq];
        }
        $stm = $this->pdo->prepare($this->reqSql);
        if ($stm === false) {
            throw new RuntimeException(
                'Impossible de préparer la requête : ' . $this->reqSql . ' dans ' . static::class
            );
        }
        $this->cache[$keyReq] = $stm;
        return $stm;
    }

    /**
     * modifie les charset pour la base de donnée
     * @param array<string|int,mixed> $elements
     * @return array<string|int,mixed>
     */
    private function controlInputEncoding(array $elements): array
    {
        foreach ($elements as $key => $value) {
            if (is_string($value)) {
                $elements[$key] = $this->changeEncoding($value, $this->charset);
            }
        }
        return $elements;
    }


    /**
     * modifie l'encodage d'une chaine
     * @param string $value
     * @param string $newCharset
     * @return string
     */
    private function changeEncoding(string $value, string $newCharset): string
    {
        $supportedCharset = [];
        $supportedCharset[] = 'UTF-32';
        $supportedCharset[] = 'UTF-16';
        $supportedCharset[] = 'UTF-8';
        $supportedCharset[] = 'CP1252';
        $supportedCharset[] = 'ISO-8859-15';
        $supportedCharset[] = 'ISO-8859-1';
        $supportedCharset[] = 'ASCII';
        $charset = mb_detect_encoding($value, $supportedCharset, true);
        if ($newCharset !== $charset) {
            return mb_convert_encoding($value, $newCharset, $charset);
        }
        return $value;
    }

    /**
     * modifie les charset pour la sortie php
     * @param array<string,mixed> $elements
     * @return array<string,mixed>
     */
    private function controlOutputEncoding(array $elements): array
    {
        foreach ($elements as $key => $value) {
            if (is_string($value)) {
                $elements[$key] = $this->changeEncoding($value, 'UTF-8');
            }
        }
        return $elements;
    }

    /**
     * Retourne tous les enregistrements
     * @param array<string|int,mixed> $param
     * @return array<string,mixed>
     */
    protected function fetchAll(array $param = []): array
    {
        $fetchAll = $this->execute($param)->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'controlOutputEncoding'], $fetchAll);
    }

    /**
     * Retourne un enregistrement
     * @param array<string|int,mixed> $param
     * @return array<string,mixed>|null
     */
    protected function fetchOne(array $param = []): ?array
    {
        $fetch = $this->execute($param)->fetch(PDO::FETCH_ASSOC);
        return $fetch !== false ? $this->controlOutputEncoding($fetch) : null;
    }

    /**
     * retourne le driver PDO de la base de donnée
     * @return string
     */
    protected function getDbDriver(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
