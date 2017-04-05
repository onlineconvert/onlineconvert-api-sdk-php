<?php
namespace OnlineConvert\Handler;

use OnlineConvert\Exception\JobFailedException;
use OnlineConvert\Model\JobStatus;

/**
 * Manage callbacks when the job finish given by the api
 *
 * @package OnlineConvert\Handler
 */
class CallbackHandler
{
    /**
     * Check if the job given have status completed
     *
     * @throws JobFailedException when the job has not been completed successfully
     *
     * @param array $job
     *
     * @return bool
     */
    public function jobHasExpectedStatus(array $job)
    {
        if ($job['status']['code'] != JobStatus::STATUS_COMPLETED) {
            $jobId = $job['id'];
            throw new JobFailedException("the job '$jobId' has not been completed successfully");
        }

        return true;
    }
}
