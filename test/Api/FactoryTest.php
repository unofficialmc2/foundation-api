<?php
declare(strict_types=1);

namespace Api;

use PHPUnit\Framework\TestCase;
use Slim\App;

/**
 * Test de la Factory
 */
class FactoryTest extends TestCase
{

    public function testCreate(): void
    {
        $app = Factory::create([
                "logger" => [
                    "name" => "test",
                    "path" => __DIR__ . "/../..",
                ]
        ]);
        self::assertInstanceOf(App::class, $app);
    }
}
