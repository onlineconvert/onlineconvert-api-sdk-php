<?php

namespace Test\OnlineConvert\SimpleJob;

/**
 * Bootstrapping of classes needed for functional testing.
 */
class SimpleJobTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const DOWNLOADS_PATH = __DIR__ . '/../Functional/downloads/';

    /**
     * @var string
     */
    const FILES_PATH = __DIR__ . '/../Functional/files/';

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
