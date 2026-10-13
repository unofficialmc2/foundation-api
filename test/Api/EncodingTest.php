<?php
declare(strict_types=1);

namespace FoundationApi;

use PDO;
use Psr\Log\NullLogger;
use Test\Fake\PdoQuery;
use Test\Fake\Query;
use Test\TestCase;

/**
 * Conversions d'encodage de PdoQueryable et Query
 */
class EncodingTest extends TestCase
{
    private const TEXTE = 'Arche MC2 accompagne des structures comme la vôtre dans la mise en œuvre';

    /**
     * @return PDO
     */
    private function sqlite(): PDO
    {
        return new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * une chaîne UTF-8 contenant « œ » sort de la base sans modification
     * @return void
     */
    public function testUtf8AvecOeInchange(): void
    {
        $fetch = (new PdoQuery($this->sqlite()))->one('SELECT :texte AS texte', ['texte' => self::TEXTE]);
        self::assertSame(self::TEXTE, $fetch['texte']);
    }

    /**
     * un texte Windows-1252 venant de la base est converti en UTF-8
     * @return void
     */
    public function testCp1252ConvertiEnUtf8(): void
    {
        // 9C : « œ » en Windows-1252
        $fetch = (new PdoQuery($this->sqlite()))->one("SELECT X'9C75767265' AS texte");
        self::assertSame('œuvre', $fetch['texte']);
    }

    /**
     * un paramètre UTF-8 est converti vers l'encodage de la base
     * @return void
     */
    public function testParametreConvertiVersCp1252(): void
    {
        $fetch = (new PdoQuery($this->sqlite(), 'CP1252'))->one('SELECT hex(:texte) AS texte', ['texte' => 'œuvre']);
        self::assertSame('9C75767265', $fetch['texte']);
    }

    /**
     * cleanerFetch : une ressource UTF-8 contenant « œ » n'est pas modifiée, et le type float est appliqué
     * @return void
     */
    public function testCleanerFetch(): void
    {
        $flux = fopen('php://memory', 'rb+');
        fwrite($flux, self::TEXTE);
        rewind($flux);
        $fetch = (new Query(new NullLogger()))->clean(
            ['TEXTE' => ['texte', 'str'], 'MONTANT' => ['montant', 'double']],
            ['TEXTE' => $flux, 'MONTANT' => '12.50']
        );
        self::assertSame(self::TEXTE, $fetch['texte']);
        self::assertSame(12.5, $fetch['montant']);
    }
}
