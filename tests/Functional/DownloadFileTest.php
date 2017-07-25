<?php

namespace Test\OnlineConvert\Functional;

use OnlineConvert\Endpoint\InputEndpoint;
use OnlineConvert\Endpoint\OutputEndpoint;
use OnlineConvert\Exception\OutputNotFound;
use Symfony\Component\Finder\Finder;

/**
 * This will test the creation of a job, correct status and download of the output.
 */
class DownloadFileTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function downloadsAJobOutputCorrectly()
    {
        $jobDefinition = [
            'input' => [
                [
                    'type' => InputEndpoint::INPUT_TYPE_REMOTE,
                    'source' => 'http://cdn.online-convert.com/images/logo-top.png'
                ]
            ],
            'conversion' => [
                [
                    'target' => 'png'
                ]
            ]
        ];

        $job = $this->api->postFullJob($jobDefinition)->getJobCreated();
        $this->api->getOutputEndpoint()->downloadOutputs($job);

        $expectedOutputCount = 1;
        $expectedFileCount   = 1;

        $this->assertCount(
            $expectedOutputCount,
            $job['output'],
            'There should be only one output for a job with one input'
        );

        $finderIterator = (new Finder())->files()
            ->in(self::DOWNLOADS_PATH . $job['output'][0]['id'])
            ->ignoreDotFiles(true)
            ->getIterator();

        $this->assertCount($expectedFileCount, $finderIterator, 'There must be one file in the output download folder');
    }

    /**
     * @test
     */
    public function failsToDownloadIfJobsContainsNoOutputs()
    {
        $this->expectException(OutputNotFound::class);

        $job = [
            'output' => [],
        ];
        $this->api->getOutputEndpoint()->downloadOutputs($job);
    }
}
