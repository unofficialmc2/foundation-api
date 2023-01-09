<?php
declare(strict_types=1);

namespace FoundationApi;

use Test\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Uri;
use Test\Fake\Formatter;

/**
 * Test de la Factory
 */
class FactoryTest extends TestCase
{

    public function testCreate(): void
    {
        $app = Factory::create([
            'logger' => ['path' => __DIR__ . "/../log", 'name' => 'test'],
            'ResponseFormatterClass' => Formatter::class
        ]);
        self::assertInstanceOf(App::class, $app);
    }

    /**
     * @return void
     */
    public function testHandle(): void
    {
        $this->expectNotToPerformAssertions();
        $app = Factory::create($this->getSettings());
        $reqFactory = new ServerRequestFactory();
        $request = $reqFactory->createServerRequest('GET', 'http://localhost/');
        $app->handle($request);
    }
}
