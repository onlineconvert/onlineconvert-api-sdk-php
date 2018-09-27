<?php

namespace Test\OnlineConvert\Functional;

use OnlineConvert\Endpoint\InputEndpoint;

/**
 * Tests general interaction with the input endpoint.
 *
 * @author Carlos Lombarte <c.lombarte@qaamgo.com>
 */
class InputEndpointTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function canPostAnInputToAJob()
    {
        $jobDefinition = [
            'conversion' => [
                [
                    'target' => 'png',
                ],
            ],
        ];

        $inputDefinition = [
            'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
            'source' => 'http://cdn.online-convert.com/images/logo-top.png',
        ];

        $jobsEndpoint = $this->api->getJobsEndpoint();
        $job          = $jobsEndpoint->postIncompleteJob($jobDefinition);

        $this->api->getInputEndpoint()->postJobInput($inputDefinition, $job);

        $job = $jobsEndpoint->processJob($job);

        $inputCount = 1;
        $this->assertCount($inputCount, $job['input']);
    }

    /**
     * @test
     */
    public function getsAllTheInputsFromAJob()
    {
        $jobDefinition = [
            'input'      => [
                [
                    'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                    'source' => 'http://cdn.online-convert.com/images/logo-top.png',
                ],
                [
                    'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                    'source' => 'http://cdn.online-convert.com/images/correct.png',
                ],
            ],
            'conversion' => [
                [
                    'target' => 'png',
                ],
            ],
        ];

        $job = $this->api->postFullJob($jobDefinition)->getJobCreated();

        $inputCount = 2;
        $inputs     = $this->api->getInputEndpoint()->getJobInputs($job['id']);

        $this->assertCount($inputCount, $inputs, "There should be $inputCount inputs");
    }

    /**
     * @test
     */
    public function canPostMultipleRemoteInputsToAJob()
    {
        $jobDefinition = [
            'conversion' => [
                [
                    'target' => 'png',
                ],
            ],
        ];

        $inputDefinition = [
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'http://cdn.online-convert.com/images/logo-top.png',
            ],
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'http://cdn.online-convert.com/images/correct.png',
            ],
        ];

        $jobsEndpoint = $this->api->getJobsEndpoint();
        $job          = $jobsEndpoint->postIncompleteJob($jobDefinition);

        $this->api->getInputEndpoint()->postJobInputs($inputDefinition, $job);

        $job = $jobsEndpoint->processJob($job);

        $inputCount = 2;
        $this->assertCount($inputCount, $job['input']);
    }

    /**
     * @test
     */
    public function canPostMultipleInputIdsInputsToAJob()
    {
        $jobDefinition = [
            'conversion' => [
                [
                    'target' => 'png',
                ],
            ],
        ];

        $inputDefinition = [
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'https://static.online-convert.com/example-file/raster%20image/jpeg/example_small.jpeg',
            ],
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'https://static.online-convert.com/example-file/raster%20image/jpeg/example_small.jpeg',
            ],
        ];

        $jobsEndpoint = $this->api->getJobsEndpoint();
        $job          = $jobsEndpoint->postIncompleteJob($jobDefinition);

        $this->api->getInputEndpoint()->postJobInputs($inputDefinition, $job);

        $job      = $jobsEndpoint->processJob($job);
        $inputId1 = $job['input'][0]['id'];
        $inputId2 = $job['input'][1]['id'];

        $jobDefinition = [
            'conversion' => [
                [
                    'target' => 'png',
                ],
            ],
        ];

        $inputDefinition = [
            [
                'type'   => InputEndpoint::INPUT_TYPE_INPUT_ID,
                'source' => $inputId1,
            ],
            [
                'type'   => InputEndpoint::INPUT_TYPE_INPUT_ID,
                'source' => $inputId2,
            ],
        ];

        $jobsEndpoint = $this->api->getJobsEndpoint();
        $job          = $jobsEndpoint->postIncompleteJob($jobDefinition);

        $this->api->getInputEndpoint()->postJobInputs($inputDefinition, $job);

        $job = $jobsEndpoint->processJob($job);

        $inputCount = 2;
        $this->assertCount($inputCount, $job['input']);
    }
}
