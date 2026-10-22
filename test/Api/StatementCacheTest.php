<?php
declare(strict_types=1);

namespace FoundationApi;

use PDO;
use Test\Fake\PdoQuery;
use Test\TestCase;

/**
 * Réutilisation des requêtes préparées par PdoQueryable
 */
class StatementCacheTest extends TestCase
{
    /**
     * hors PDO_OCI, une même requête réutilise la requête préparée
     * @return void
     */
    public function testRequetePrepareeReutilisee(): void
    {
        $query = new PdoQuery(new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
        self::assertSame($query->statement('SELECT 1'), $query->statement('SELECT 1'));
    }

    /**
     * deux requêtes différentes ne partagent pas la même requête préparée
     * @return void
     */
    public function testRequetesDifferentesNonPartagees(): void
    {
        $query = new PdoQuery(new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
        self::assertNotSame($query->statement('SELECT 1'), $query->statement('SELECT 2'));
    }
}
