<?php
declare(strict_types=1);

namespace Test;

use DateTime;
use DateTimeInterface;
use Exception;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Class TestCase de base pour les tests du projet
 */
class TestCase extends PhpUnitTestCase
{
    protected Generator $fake;

    /**
     * TestCase constructor.
     * @inheritDoc
     * @phpstan-ignore-next-line : problème de doc de PhpUnit
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->fake = Factory::create('fr_FR');
    }

    /**
     * test l'absence  de plusieurs clé
     * @param array<string> $notExpectedKeys
     * @param array<string,mixed> $actual
     * @param string $message
     */
    protected static function assertArrayNotHasKeys(array $notExpectedKeys, array $actual, string $message = ""): void
    {
        foreach ($notExpectedKeys as $key) {
            self::assertArrayNotHasKey($key, $actual, $message);
        }
    }

    /**
     * test la présence de plusieurs clé
     * @param string[] $expectedKeys
     * @param array<string,mixed> $actual
     * @param string $message
     */
    protected static function assertArrayHasKeys(array $expectedKeys, array $actual, string $message = ""): void
    {
        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $actual, $message);
        }
    }

    /**
     * test si la donnée est une Date au bon format
     * @param string $format
     * @param string $actual
     * @param string $message
     * @retrun void
     */
    public static function assertFormattedDate(string $format, string $actual, string $message = ""): void
    {
        try {
            /** @var \DateTime|false $date */
            $date = DateTime::createFromFormat($format, $actual);
        } catch (Exception $e) {
            $date = false;
        }
        self::assertInstanceOf(DateTime::class, $date, $message);
    }

    /**
     * @param string $urlCheck
     * @param string $message
     */
    public static function assertIsUrlString(string $urlCheck, string $message = ""):void
    {
        $url = filter_var($urlCheck, FILTER_SANITIZE_URL);
        $result = filter_var($url, FILTER_VALIDATE_URL) !== false;
        self::assertTrue($result, $message);
    }
}
