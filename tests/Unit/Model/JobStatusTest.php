<?php

namespace Test\OnlineConvert\Unit\Endpoint;

use OnlineConvert\Model\JobStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class JobStatusTest
 * @package Test\OnlineConvert\Endpoint
 */
class JobStatusTest extends TestCase
{
    /**
     * Data provider for testStatus
     *
     * @return array
     */
    public function statusProvider()
    {
        return [
            'Testing status "incomplete"' => [
                'status'            => 'incomplete',
                'canBeUpdatedBy'    => 'ready',
                'canNotBeUpdatedBy' => null,
                'isCompleted'       => false,
                'isFailed'          => false,
            ],
            'Testing status "ready"' => [
                'status'            => 'ready',
                'canBeUpdatedBy'    => 'downloading',
                'canNotBeUpdatedBy' => 'incomplete',
                'isCompleted'       => false,
                'isFailed'          => false,
            ],
            'Testing status "downloading"' => [
                'status'            => 'downloading',
                'canBeUpdatedBy'    => 'processing',
                'canNotBeUpdatedBy' => 'incomplete',
                'isCompleted'       => false,
                'isFailed'          => false,
            ],
            'Testing status "processing"' => [
                'status'            => 'processing',
                'canBeUpdatedBy'    => 'completed',
                'canNotBeUpdatedBy' => 'incomplete',
                'isCompleted'       => false,
                'isFailed'          => false,
            ],
            'Testing status "failed"' => [
                'status'            => 'failed',
                'canBeUpdatedBy'    => 'completed',
                'canNotBeUpdatedBy' => 'incomplete',
                'isCompleted'       => false,
                'isFailed'          => true,
            ],
            'Testing status "completed"' => [
                'status'            => 'completed',
                'canBeUpdatedBy'    => null,
                'canNotBeUpdatedBy' => 'incomplete',
                'isCompleted'       => true,
                'isFailed'          => false,
            ],
        ];
    }

    /**
     * Test Status behavior
     *
     * @dataProvider statusProvider
     *
     * @param string  $statusCode
     * @param string  $canBeUpdatedByCode
     * @param string  $canNotBeUpdatedByCode
     * @param boolean $isCompleted
     * @param boolean $isFailed
     */
    public function testStatus($statusCode, $canBeUpdatedByCode, $canNotBeUpdatedByCode, $isCompleted, $isFailed)
    {
        $status = new JobStatus($statusCode);

        $this->assertEquals($statusCode, $status->getCode());
        $this->assertEquals($isCompleted, $status->isStatus(JobStatus::STATUS_COMPLETED));
        $this->assertEquals($isFailed, $status->isStatus(JobStatus::STATUS_FAILED));

        if ($canBeUpdatedByCode) {
            $canBeUpdatedBy = new JobStatus($canBeUpdatedByCode);
            $this->assertTrue($status->canBeUpdated($canBeUpdatedBy));
        }

        if ($canNotBeUpdatedByCode) {
            $canNotBeUpdatedBy = new JobStatus($canNotBeUpdatedByCode);
            $this->assertFalse($status->canBeUpdated($canNotBeUpdatedBy));
        }
    }

    public function testUnknownStatus()
    {
        $wrongStatus = 'wrongStatus';

        $this->expectException('OnlineConvert\Exception\StatusUnknownException');
        $this->expectExceptionMessage('Unknown status: ' . $wrongStatus);

        new JobStatus($wrongStatus);
    }

    public function testIsStatus()
    {
        $status = new JobStatus(JobStatus::STATUS_PROCESSING);

        $this->assertTrue($status->isStatus(JobStatus::STATUS_PROCESSING));
        $this->assertFalse($status->isStatus(JobStatus::STATUS_READY));
        $this->assertFalse($status->isStatus(JobStatus::STATUS_COMPLETED));
    }
}
