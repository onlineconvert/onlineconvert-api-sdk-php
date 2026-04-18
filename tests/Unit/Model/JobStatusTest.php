<?php

namespace Test\OnlineConvert\Unit\Endpoint;

use OnlineConvert\Model\JobStatus;
use PHPUnit\Framework\Attributes\DataProvider;
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
    public static function statusProvider()
    {
        return [
            'Testing status "incomplete"' => [
                'incomplete',
                'ready',
                null,
                false,
                false,
            ],
            'Testing status "ready"' => [
                'ready',
                'downloading',
                'incomplete',
                false,
                false,
            ],
            'Testing status "downloading"' => [
                'downloading',
                'processing',
                'incomplete',
                false,
                false,
            ],
            'Testing status "processing"' => [
                'processing',
                'completed',
                'incomplete',
                false,
                false,
            ],
            'Testing status "failed"' => [
                'failed',
                'completed',
                'incomplete',
                false,
                true,
            ],
            'Testing status "completed"' => [
                'completed',
                null,
                'incomplete',
                true,
                false,
            ],
        ];
    }

    #[DataProvider('statusProvider')]
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
