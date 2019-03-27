<?php

namespace Test\OnlineConvert\SimpleJob;


/**
 * Bootstrapping of classes needed for functional testing.
 */
class SimpleJobTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @const string
     */
    const DOWNLOADS_PATH = __DIR__ . '/downloads/';

    /**
     * @const string
     */
    const FILES_PATH = __DIR__ . '/files/';


    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
   
    }


    
}
