<?php

namespace Test\OnlineConvert\SimpleJob;

use OnlineConvert\SimpleJob;

/**
 * This will test the creation of a job, wait for completed status and download of the output.
 */
class SimpleJobTest extends SimpleJobTestCase
{
    /**
     * @test
     */
    public function testSimple()
    {

        $SimpleJob = new SimpleJob("a304b235bf006f837ea82341592ca889");
        $outputId = $SimpleJob->download('http://cdn.online-convert.com/images/logo-top.png')
            ->upload(SimpleJobTest::FILES_PATH . "oc_logo.png")
            ->setTarget("jpg")
            ->setOptions([
                "width" => 1200,
                "height" => 1000,
            ])
            ->start()
            ->wait()
            ->saveTo(SimpleJobTest::DOWNLOADS_PATH);

        $this->assertTrue(file_exists(SimpleJobTest::DOWNLOADS_PATH . $outputId));
    }

}
