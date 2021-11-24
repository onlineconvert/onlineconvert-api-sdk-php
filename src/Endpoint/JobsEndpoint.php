<?php

namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Exception\CallbackNotDefinedException;
use OnlineConvert\Exception\InvalidArgumentException;
use OnlineConvert\Exception\InvalidStatusException;
use OnlineConvert\Exception\JobFailedException;
use OnlineConvert\Exception\JobNotFoundException;
use OnlineConvert\Exception\OnlineConvertSdkException;
use OnlineConvert\Model\JobStatus;

/**
 * Manage Jobs Endpoint
 *
 * @package OnlineConvert\Endpoint
 */
class JobsEndpoint extends Abstracted
{
    /**
     * Status when the job finish correctly
     *
     * @var string
     * @deprecated Will be removed in v3.0
     */
    const STATUS_COMPLETED = 'completed';

    /**
     * Status when the job fails
     *
     * @var string
     * @deprecated Will be removed in v3.0
     */
    const STATUS_FAILED = 'failed';

    /**
     * Status when the job is ready to begin process
     *
     * @var string
     * @deprecated Will be removed in v3.0
     */
    const STATUS_READY = 'ready';

    /**
     * Status when the job is incomplete waiting for information to be ready or processed
     *
     * @var string
     * @deprecated Will be removed in v3.0
     */
    const STATUS_INCOMPLETE = 'incomplete';

    /**
     * Default time in seconds to wait between job status requests
     *
     * @var int
     */
    const DEFAULT_WAITING_TIME_BETWEEN_REQUESTS = 1;

    /**
     * Maximum time allowed for a status to be reached (14400 = 4 hours)
     *
     * @var int
     */
    const MAX_WAITING_TIME = 14400;

    /**
     * Determine if this API Handler will be async or sync
     *
     * @var bool
     */
    private $async;

    /**
     * Post a full job
     * Notice that this handles the 'process' flag in the job automatically
     *
     * @api
     *
     * @throws OnlineConvertSdkException When there is an error on the request
     * @throws InvalidArgumentException  If the passed job missed mandatory fields
     *
     * @param array $job
     *
     * @return array
     */
    public function postFullJob(array $job)
    {
        if (empty($job)) {
            throw new InvalidArgumentException('The Job is empty');
        }

        if (empty($job['input'])) {
            throw new InvalidArgumentException(
                'It is not possible to use ' . __METHOD__ .
                ' with no input. Please, use ' . __CLASS__ . '::postIncompleteJob instead.'
            );
        }

        $this->checkJobCallbackForAsync($job);

        $uploadInput = [];
        $remoteInput = [];
        $withUpload  = false;

        foreach ($job['input'] as $key => $input) {
            if ($input['type'] == InputEndpoint::INPUT_TYPE_UPLOAD) {
                $uploadInput[] = $input;
                $withUpload    = true;
            } elseif ($input['type'] == InputEndpoint::INPUT_TYPE_REMOTE
                || $input['type'] == InputEndpoint::INPUT_TYPE_INPUT_ID
                || $input['type'] == InputEndpoint::INPUT_TYPE_CLOUD) {
                $remoteInput[] = $input;
            }
            unset($job['input'][$key]);
        }

        $job['input'] = $remoteInput;

        if ($withUpload) {
            $job['process'] = false;
        }

        $job = $this->responseToArray(
            $this->client->sendRequest(Resources::JOB, Interfaced::METHOD_POST, $job)
        );

        if ($withUpload) {
            $statusToWait = [JobStatus::STATUS_READY];

            if (count($remoteInput) == 0) {
                $statusToWait[] = JobStatus::STATUS_INCOMPLETE;
            }

            $job = $this->waitStatus($job['id'], $statusToWait);

            foreach ($uploadInput as $key => $input) {
                $this->postFile($input, $job);
            }

            $job = $this->waitStatus($job['id'], [JobStatus::STATUS_READY]);

            $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);

            $job['process'] = true;
            $job            = $this->responseToArray(
                $this->client->sendRequest($url, Interfaced::METHOD_PATCH, ['process' => true])
            );
        }

        if (!$this->async) {
            $this->waitStatus($job['id'], [JobStatus::STATUS_COMPLETED]);
        }

        return $this->getJob($job['id']);
    }

    /**
     * Post a incomplete job to can modify it after works
     * Notice that this handle the 'process' flag in the job automatically
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param array $job
     *
     * @return array
     */
    public function postIncompleteJob(array $job)
    {
        $this->checkJobCallbackForAsync($job);

        $job['process'] = false;

        return $this->responseToArray(
            $this->client->sendRequest(Resources::JOB, Interfaced::METHOD_POST, $job)
        );
    }

    /**
     * Loop to check if the job has a specific or a later status
     *
     * @api
     *
     * @throws OnlineConvertSdkException When an error was catched on the API request
     * @throws JobFailedException        If the Job failed, regardless of the awaited statuses
     * @throws JobNotFoundException      If the passed Job is missed or incomplete
     * @throws InvalidArgumentException  When the method is called with wrong arguments
     * @throws InvalidStatusException    When the awaited statuses cannot be reached anymore
     *
     * @param string  $jobId                      The job id
     * @param array   $waitFor                    Array containing all the possibles statuses to wait for
     * @param integer $waitingTimeBetweenRequests Time to wait between requests in seconds
     * @param integer $timeout                    Maximum time to wait for a status to be reached
     *
     * @return array
     */
    public function waitStatus(
        $jobId,
        array $waitFor,
        $waitingTimeBetweenRequests = self::DEFAULT_WAITING_TIME_BETWEEN_REQUESTS,
        $timeout = self::MAX_WAITING_TIME
    ) {
        if (empty($jobId)) {
            throw new JobNotFoundException();
        }

        if (empty($waitFor)) {
            throw new InvalidArgumentException('No statuses provided to wait for');
        }

        $waitingTimeBetweenRequests = (int) $waitingTimeBetweenRequests;
        $waitingTimeBetweenRequests = ($waitingTimeBetweenRequests >= 1) ?
            $waitingTimeBetweenRequests :
            self::DEFAULT_WAITING_TIME_BETWEEN_REQUESTS;

        $timeout = (int) $timeout;
        $timeout = ($timeout <= self::MAX_WAITING_TIME) ?
            $timeout :
            self::MAX_WAITING_TIME;

        $response = null;
        $url      = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $jobId]);

        $higherAwaitedStatus = new JobStatus(JobStatus::STATUS_INCOMPLETE);
        foreach ($waitFor as $awaitedStatusCode) {
            $newStatus           = new JobStatus($awaitedStatusCode);
            $higherAwaitedStatus = $higherAwaitedStatus->canBeUpdated($newStatus) ? $newStatus : $higherAwaitedStatus;
        }

        $i = 0;
        do {
            $response = $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            );

            $responseAsArray = $this->responseToArray($response);
            $actualStatus    = new JobStatus($responseAsArray['status']['code']);

            if (in_array($actualStatus->getCode(), $waitFor, true)) {
                return $responseAsArray;
            }

            if (!$actualStatus->canBeUpdated($higherAwaitedStatus)) {
                throw new InvalidStatusException(
                    'The awaited status ' . $higherAwaitedStatus->getCode() .
                    ' can never be reached since the actual status is ' . $actualStatus->getCode());
            }

            if ($actualStatus->isStatus(JobStatus::STATUS_FAILED)) {
                throw new JobFailedException(json_encode($response));
            }

            sleep($waitingTimeBetweenRequests);
            $i += $waitingTimeBetweenRequests;
        } while ($i <= $timeout);

        throw new OnlineConvertSdkException('Timeout reached while waiting for the Job status');
    }

    /**
     * Start to process a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException When error on the request
     * @throws JobNotFoundException      If the passed Job is missed or incomplete
     *
     * @param array $job if this is not defined will take the last one created
     *
     * @return array
     */
    public function processJob(array $job)
    {
        if (empty($job['id'])) {
            throw new JobNotFoundException();
        }

        $this->waitStatus(
            $job['id'],
            [
                JobStatus::STATUS_READY,
                JobStatus::STATUS_INCOMPLETE,
                JobStatus::STATUS_DOWNLOADING,
            ]
        );

        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);

        $this->client->sendRequest(
            $url,
            Interfaced::METHOD_PATCH,
            ['process' => true],
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        if (!$this->async) {
            return $this->waitStatus($job['id'], [JobStatus::STATUS_COMPLETED]);
        }

        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Check if the async flag is true and if the job have the callback defined required to async jobs
     *
     * @throws CallbackNotDefinedException when the async flag is done and the job have not callback defined
     *
     * @param array $job
     *
     * @return void
     */
    protected function checkJobCallbackForAsync(array $job)
    {
        if ($this->async) {
            if (!isset($job['callback']) || !filter_var($job['callback'], FILTER_VALIDATE_URL)) {
                throw new CallbackNotDefinedException(
                    'Async jobs must have a valid callback url to notify when the job finish'
                );
            }
        }
    }

    /**
     * Set if the jobs will be async or not
     *
     * @param boolean $async
     */
    public function setAsync($async)
    {
        $this->async = $async;
    }

    /**
     * Post local file
     *
     * @api
     *
     * @param array $input
     * @param array $job
     *
     * @return array when error on the request
     */
    private function postFile(array $input, array $job)
    {
        $url = $this->client->generateUrl(
            Resources::URL_POST_FILE,
            [
                'server' => $job['server'],
                'job_id' => $job['id'],
            ]
        );

        return $this->responseToArray($this->client->postLocalFile($input['source'], $url, $job['token']));
    }

    /**
     * Get list of all the jobs for an API Key
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->getJobsByStatusWithDefault();
    }

    /**
     * Get list of all the jobs with a specific status for an API Key
     *
     * @api
     *
     * @param string $status
     *
     * @return array when error on the request
     *
     */
    public function getJobsByStatus($status)
    {
        return $this->getJobsByStatusWithDefault($status);
    }

    /**
     * Get list of all the jobs with a specific status for an API Key
     *
     * @api
     *
     * @param string $status
     *
     * @return array when error on the request
     *
     */
    private function getJobsByStatusWithDefault($status = '')
    {
        $jobStatusParameter = ((!empty($status)) ? '?status=' . $status : '');

        $response = $this->client->sendRequest(
            Resources::JOB . $jobStatusParameter,
            Interfaced::METHOD_GET,
            null,
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return $this->responseToArray($response);
    }

    /**
     * Post a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function postJob(array $job)
    {
        $response = $this->client->sendRequest(
            Resources::JOB,
            Interfaced::METHOD_POST,
            $job
        );

        return $this->responseToArray($response);
    }

    /**
     * Patch a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function patchJob($jobId, array $job)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $jobId]);

        $response = $this->client->sendRequest(
            $url,
            Interfaced::METHOD_PATCH,
            $job,
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return $this->responseToArray($response);
    }

    /**
     * Get a job by the given id
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJob($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Delete a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when the deletion was not successfully - request error
     *
     * @param string $jobId
     *
     * @return bool TRUE when the deletion was successfully
     */
    public function deleteJob($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $jobId]);
        $this->client->sendRequest(
            $url,
            Interfaced::METHOD_DELETE,
            [],
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return true;
    }

    /**
     * Get the threads of a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobThreads($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_THREADS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Get the history of a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobHistory($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_HISTORY, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }
}
