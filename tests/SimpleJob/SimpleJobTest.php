<?php

namespace Test\OnlineConvert\SimpleJob;

use OnlineConvert\SimpleJob;
use Symfony\Component\Finder\Finder;

/**
 * This will test the creation of a job, wait for completed status and download of the output.
 */
class SimpleJobTest extends SimpleJobTestCase
{
    /**
     * @test
     */
    public function downloadsAJobOutputCorrectly()
    {
        $ApiKey    = getenv('OC_API_KEY');
        $SimpleJob = new SimpleJob($ApiKey);

        $job = $SimpleJob
            ->download('http://cdn.online-convert.com/images/logo-top.png')
            ->upload(self::FILES_PATH . 'oc_logo.png')
            ->addConversion(
                'jpg',
                [
                    'width'  => 1200,
                    'height' => 1000,
                ]
            )
            ->start()
            ->wait()
            ->saveTo(self::DOWNLOADS_PATH);

        $expectedOutputCount = 1;
        $expectedFileCount   = 1;

        $this->assertCount(
            $expectedOutputCount,
            $job['output'],
            'There should be only one output for a job with two inputs'
        );

        $dirs = [self::DOWNLOADS_PATH . $job['output'][0]['id']];

        $finder = new Finder();

        $finderIterator = $finder
            ->files()
            ->in($dirs)
            ->ignoreDotFiles(true)
            ->getIterator();

        $this->assertCount(
            $expectedFileCount,
            $finderIterator,
            'There must be one file in the output download folder'
        );
    }
}
