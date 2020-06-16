<?php

namespace Test\OnlineConvert\SimpleJob;

use PHPUnit\Framework\TestCase;

/**
 * Bootstrapping of classes needed for functional testing.
 */
class SimpleJobTestCase extends TestCase
{
    /**
     * @var string
     */
    const DOWNLOADS_PATH = __DIR__ . '/../Functional/downloads/';

    /**
     * @var string
     */
    const FILES_PATH = __DIR__ . '/../Functional/files/';

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
