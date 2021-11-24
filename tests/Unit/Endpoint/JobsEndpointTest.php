<?php

namespace Test\OnlineConvert\Unit\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Client\Resources as ClientResources;
use OnlineConvert\Endpoint\JobsEndpoint;
use OnlineConvert\Endpoint\Resources;

/**
 * Class JobsEndpointTest
 * @package Test\OnlineConvert\Endpoint
 */
class JobsEndpointTest extends Abstracted
{
    /**
     * @var JobsEndpoint
     */
    private $jobsEndpoint;

    public function setUp(): void
    {
        parent::setUp();

        $this->jobsEndpoint = new JobsEndpoint($this->clientMock);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->jobsEndpoint);
    }

    public function testWaitStatusForMatchedSingleStatus()
    {
        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"ready"}}');

        $response = $this->jobsEndpoint->waitStatus($this->aJobId, ['ready']);

        $this->assertEquals('ready', $response['status']['code']);
    }

    public function testWaitStatusForMatchedMultipleStatuses()
    {
        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"downloading"}}');

        $response = $this->jobsEndpoint->waitStatus($this->aJobId, ['ready', 'downloading', 'completed']);

        $this->assertEquals('downloading', $response['status']['code']);
    }

    public function testWaitStatusForLowerStatus()
    {
        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"processing"}}');

        $this->expectException('OnlineConvert\Exception\InvalidStatusException');
        $this->expectExceptionMessage(
            'The awaited status ready can never be reached since the actual status is processing'
        );

        $this->jobsEndpoint->waitStatus($this->aJobId, ['ready']);
    }

    public function testWaitStatusForLowerStatusButJobFailed()
    {
        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"failed"}}');

        $this->expectException('OnlineConvert\Exception\JobFailedException');
        $this->expectExceptionMessage(
            '"{\"id\":\"' . $this->aJobId . '\",\"status\":{\"code\":\"failed\"}}"'
        );

        $this->jobsEndpoint->waitStatus($this->aJobId, ['completed']);
    }

    public function testWaitStatusForHigherStatus()
    {
        $this->clientMock
            ->expects($this->exactly(3))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                '{"id":"' . $this->aJobId . '","status":{"code":"downloading"}}',
                '{"id":"' . $this->aJobId . '","status":{"code":"processing"}}',
                '{"id":"' . $this->aJobId . '","status":{"code":"completed"}}'
            );

        $response = $this->jobsEndpoint->waitStatus($this->aJobId, ['completed']);

        $this->assertEquals('completed', $response['status']['code']);
    }

    public function testWaitStatusForTimeoutException()
    {
        $waitingTimeBetweenRequests = 1;
        $timeout                    = 2;

        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"ready"}}');

        $this->expectException('OnlineConvert\Exception\OnlineConvertSdkException');
        $this->expectExceptionMessage('Timeout reached while waiting for the Job status');

        $response = $this->jobsEndpoint->waitStatus(
            $this->aJobId,
            ['completed'],
            $waitingTimeBetweenRequests,
            $timeout
        );

        $this->assertEquals('ready', $response['status']['code']);
    }

    public function testPostFullJobWithEmptyJob()
    {
        $this->expectException('OnlineConvert\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The Job is empty');

        $this->setClientMockToNeverCallMethods();

        $this->jobsEndpoint->postFullJob([]);
    }

    public function testPostFullJobWithEmptyInput()
    {
        $job = [
            'process' => false,
        ];

        $this->setClientMockToNeverCallMethods();

        $this->expectException('OnlineConvert\Exception\InvalidArgumentException');
        $this->expectExceptionMessage(
            'It is not possible to use OnlineConvert\Endpoint\JobsEndpoint::postFullJob with no input. ' .
            'Please, use OnlineConvert\Endpoint\JobsEndpoint::postIncompleteJob instead.'
        );

        $this->jobsEndpoint->postFullJob($job);
    }

    public function testPostFullJobAsyncWithoutCallback()
    {
        $job = [
            'process' => false,
            'input' => [
                [
                    'type'   => 'remote',
                    'source' => 'http://www.online-convert.com',
                ],
            ],
        ];

        $this->setClientMockToNeverCallMethods();

        $this->expectException('OnlineConvert\Exception\CallbackNotDefinedException');
        $this->expectExceptionMessage('Async jobs must have a valid callback url to notify when the job finish');

        $this->jobsEndpoint->setAsync(true);
        $this->jobsEndpoint->postFullJob($job);
    }

    public function testPostFullJobWithRemoteInput()
    {
        $job = [
            'process' => true,
            'input' => [
                [
                    'type'   => 'remote',
                    'source' => 'http://www.online-convert.com',
                ],
            ],
        ];

        $this->clientMock
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn('{"id":"' . $this->aJobId . '","status":{"code":"completed"}}');

        $createdJob = $this->jobsEndpoint->postFullJob($job);

        $this->assertArrayHasKey('id', $createdJob);
        $this->assertArrayHasKey('status', $createdJob);
    }

    public function testPostIncompleteSyncJobWillChangeProcessToFalse()
    {
        $job = [
            'process' => true,
        ];

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(Resources::JOB, Interfaced::METHOD_POST, ['process' => false]);

        $this->jobsEndpoint->postIncompleteJob($job);
    }

    public function testGetJobs()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                Resources::JOB,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $this->jobsEndpoint->getJobs();
    }

    public function testGetJobByStatus()
    {
        $statusFilter = 'completed';

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                Resources::JOB . '?status=' . $statusFilter,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $this->jobsEndpoint->getJobsByStatus($statusFilter);
    }

    public function testPostJob()
    {
        $job = [
            'process' => true,
            'input' => [
                [
                    'type'   => 'remote',
                    'source' => 'http://www.online-convert.com',
                ],
            ],
        ];

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                Resources::JOB,
                Interfaced::METHOD_POST,
                $job
            );

        $this->jobsEndpoint->postJob($job);
    }

    public function testPatchJob()
    {
        $job = [
            'process' => true,
        ];

        $urlToCall = ClientResources::HTTP_HOST . '/jobs/' . $this->aJobId;

        $this->clientMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                Resources::JOB_ID,
                ['job_id' => $this->aJobId]
            )
            ->willReturn($urlToCall);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $urlToCall,
                Interfaced::METHOD_PATCH,
                $job,
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $this->jobsEndpoint->patchJob($this->aJobId, $job);
    }

    public function testDeleteJob()
    {
        $urlToCall =  '/jobs/' . $this->aJobId;

        $this->clientMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                Resources::JOB_ID,
                ['job_id' => $this->aJobId]
            )
            ->willReturn($urlToCall);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $urlToCall,
                Interfaced::METHOD_DELETE,
                [],
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $response = $this->jobsEndpoint->deleteJob($this->aJobId);

        $this->assertTrue($response);
    }

    public function testGetJobThreads()
    {
        $urlToCall =  '/jobs/' . $this->aJobId. '/threads';

        $this->clientMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                Resources::JOB_ID_THREADS,
                ['job_id' => $this->aJobId]
            )
            ->willReturn($urlToCall);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $urlToCall,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $this->jobsEndpoint->getJobThreads($this->aJobId);
    }

    public function testGetJobHistory()
    {
        $urlToCall =  '/jobs/' . $this->aJobId. '/history';

        $this->clientMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                Resources::JOB_ID_HISTORY,
                ['job_id' => $this->aJobId]
            )
            ->willReturn($urlToCall);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $urlToCall,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => null]
            );

        $this->jobsEndpoint->getJobHistory($this->aJobId);
    }
}
