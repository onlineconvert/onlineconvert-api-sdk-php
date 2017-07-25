<?php

namespace Test\OnlineConvert\Functional;

use OnlineConvert\Endpoint\InputEndpoint;
use OnlineConvert\Model\JobStatus;

/**
 * Tests the job interaction with the different endpoints.
 */
class JobInteractionTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function jobWithFileUploadIsCompleted()
    {
        $jobDefinition = [
            'input' => [
                [
                    'type' => InputEndpoint::INPUT_TYPE_UPLOAD,
                    'source' => self::FILES_PATH . 'oc_logo.png'
                ]
            ],
            'conversion' => [
                [
                    'target' => 'jpg'
                ]
            ]
        ];

        $job = $this->api->postFullJob($jobDefinition)->getJobCreated();

        $outputCount = 1;
        $this->assertEquals(JobStatus::STATUS_COMPLETED, $job['status']['code']);
        $this->assertCount($outputCount, $job['output']);
    }

    /**
     * @test
     */
    public function createEmptyJobAndUpdateWithNewInput()
    {
        $jobsEndpoint  = $this->api->getJobsEndpoint();
        $inputUrl      = 'http://cdn.online-convert.com/images/logo-top.png';
        $jobDefinition = [
            'conversion' => [
                [
                    'target' => 'png'
                ]
            ]
        ];

        $job = $jobsEndpoint->postIncompleteJob($jobDefinition);

        $this->api->getInputEndpoint()->postJobInputRemote($inputUrl, $job['id']);
        $finishedJob = $jobsEndpoint->processJob($job);

        $this->assertEquals(
            JobStatus::STATUS_COMPLETED,
            $finishedJob['status']['code'],
            'The job status should be completed'
        );
    }

    /**
     * @test
     */
    public function getsTheLatestCompletedJobs()
    {
        $jobs = $this->api->getJobsEndpoint()->getJobsByStatus(JobStatus::STATUS_COMPLETED);

        $this->assertGreaterThan(0, count($jobs), 'There should be many jobs with status completed');
        $this->assertEquals(JobStatus::STATUS_COMPLETED, $jobs[0]['status']['code']);
    }

    /**
     * @test
     */
    public function canDeleteJobOutputs()
    {
        $jobDefinition = [
            'input' => [
                [
                    'type' => InputEndpoint::INPUT_TYPE_UPLOAD,
                    'source' => self::FILES_PATH . 'oc_logo.png'
                ]
            ],
            'conversion' => [
                [
                    'target' => 'jpg'
                ]
            ]
        ];

        $job           = $this->api->postFullJob($jobDefinition)->getJobCreated();
        $outputDeleted = $this->api->getOutputEndpoint()->deleteJobOutput($job['id'], $job['output'][0]['id']);

        $this->assertTrue($outputDeleted, 'The output should have been deleted but something failed');
    }

    /**
     * @test
     */
    public function canGetTheOutputInformation()
    {
        $jobDefinition = [
            'input' => [
                [
                    'type' => InputEndpoint::INPUT_TYPE_UPLOAD,
                    'source' => self::FILES_PATH . 'oc_logo.png'
                ]
            ],
            'conversion' => [
                [
                    'target' => 'jpg'
                ]
            ]
        ];

        $job     = $this->api->postFullJob($jobDefinition)->getJobCreated();
        $outputs = $this->api->getOutputEndpoint()->getJobOutputs($job['id']);

        $outputCount = 1;
        $this->assertCount($outputCount, $outputs, 'The job must have only output');

        $output = array_pop($outputs);

        $this->assertArrayHasKey('uri', $output, 'The output must have a uri where you can download it');
        $this->assertArrayHasKey('status', $output, 'The output mast have an status');
        $this->assertArrayHasKey('content_type', $output, 'The output mast have a content type');
        $this->assertArrayHasKey(
            'source',
            $output,
            'The output mast have a source key that tells which conversion triggered it'
        );
        $this->assertArrayHasKey('conversion', $output['source'], 'We must know from which conversion is generated');
        $this->assertArrayHasKey('input', $output['source'], 'We must know from which input is generated');
    }
}
