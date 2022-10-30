<?php

namespace Api;

use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public static function testAddMiddleware(): void
    {
        $app = Factory::create([
            "logger"=>["name"=>"test","path" => __DIR__ . "/../log"]
        ]);
    }
}
