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
                    'target' => 'png'
                ]
            ]
        ];
        $inputDefinition = [
            'type' => InputEndpoint::INPUT_TYPE_REMOTE,
            'source' => 'http://cdn.online-convert.com/images/logo-top.png'
        ];
        $jobsEndpoint = $this->api->getJobsEndpoint();

        $job = $jobsEndpoint->postIncompleteJob($jobDefinition);
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
            'input' => [
                [
                    'type' => InputEndpoint::INPUT_TYPE_REMOTE,
                    'source' => 'http://cdn.online-convert.com/images/logo-top.png'
                ],
                [
                    'type' => InputEndpoint::INPUT_TYPE_REMOTE,
                    'source' => 'http://cdn.online-convert.com/images/correct.png'
                ],
            ],
            'conversion' => [
                [
                    'target' => 'png'
                ]
            ]
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
                    "target"   => 'png',
                ]
            ]
        ];

        $inputDefinition = [
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'http://cdn.online-convert.com/images/logo-top.png'
            ],
            [
                'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
                'source' => 'http://cdn.online-convert.com/images/correct.png'
            ]
        ];
        $jobsEndpoint = $this->api->getJobsEndpoint();

        $job = $jobsEndpoint->postIncompleteJob($jobDefinition);
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
                    "target"   => 'png',
                ]
            ]
        ];

        $inputDefinition = [
            [
                'type'   => InputEndpoint::INPUT_TYPE_INPUT_ID,
                'source' => 'c9497281-7518-4905-949d-b191a1b481c7'
            ],
            [
                'type'   => InputEndpoint::INPUT_TYPE_INPUT_ID,
                'source' => 'cfa75d10-fed2-4d32-9f34-017a95c1b62a'
            ]
        ];
        $jobsEndpoint = $this->api->getJobsEndpoint();

        $job = $jobsEndpoint->postIncompleteJob($jobDefinition);
        $this->api->getInputEndpoint()->postJobInputs($inputDefinition, $job);
        $job = $jobsEndpoint->processJob($job);

        $inputCount = 2;
        $this->assertCount($inputCount, $job['input']);
    }
}
