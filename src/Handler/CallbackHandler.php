<?php
namespace OnlineConvert\Handler;

use OnlineConvert\Endpoint\JobsEndpoint;
use OnlineConvert\Exception\JobFailedException;

/**
 * Manage callbacks when the job finish given by the api
 *
 * @package OnlineConvert\Handler
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
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
        if ($job['status']['code'] != JobsEndpoint::STATUS_COMPLETED) {
            $jobId = $job['id'];
            throw new JobFailedException("the job '$jobId' has not been completed successfully");
        }

        return true;
    }
}