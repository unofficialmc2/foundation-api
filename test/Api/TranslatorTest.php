<?php
declare(strict_types=1);

namespace FoundationApi;

use Test\TestCase;

/**
 * Class de test pour Translator
 */
class TranslatorTest extends TestCase
{
    const DIRECTORY = __DIR__. "/../translate/";
    /**
     * test Constructor
     * @return void
     */
    public function testConstructor(): void
    {
        if (is_file(self::DIRECTORY . 'lang_fr.json')) {
            unlink(self::DIRECTORY . 'lang_fr.json');
        }
        $t = new Translator(self::DIRECTORY, 'fr');
        $t('test de ' . __METHOD__);
        self::assertFileExists(self::DIRECTORY . 'lang_fr.json');
        $t2 = new Translator(self::DIRECTORY, 'fr');
        $t2('test2 de ' . __METHOD__);
        self::assertFileExists(self::DIRECTORY . 'lang_fr.json');
    }
}
