<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\CallbackNotDefinedException;
use OnlineConvert\Exception\JobFailedException;
use OnlineConvert\Exception\OnlineConvertSdkException;

/**
 * Manage Jobs Endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class JobsEndpoint extends Abstracted
{
    /**
     * Status when the job finish correctly
     *
     * @const string
     */
    const STATUS_COMPLETED = 'completed';

    /**
     * Status when the job fails
     *
     * @const string
     */
    const STATUS_FAILED = 'failed';

    /**
     * Status when the job is ready to begin process
     *
     * @const string
     */
    const STATUS_READY = 'ready';

    /**
     * Determine if this API Handler will be async or sync
     *
     * @var bool
     */
    private $async;

    /**
     * Get list of all the jobs for a API Key
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function getJobs()
    {
        $response = $this->client->sendRequest(Resources::JOB, OnlineConvertClient::METHOD_GET);

        return $this->responseToArray($response);
    }

    /**
     * Post a full job
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
    public function postFullJob(array $job)
    {
        $this->checkJobCallbackForAsync($job);

        $uploadInput = [];
        $remoteInput = [];
        $withUpload  = false;

        foreach ($job['input'] as $key => $input) {
            if ($input['type'] == InputEndpoint::INPUT_TYPE_UPLOAD) {
                $uploadInput[] = $input;
                $withUpload    = true;
            } elseif ($input['type'] == InputEndpoint::INPUT_TYPE_REMOTE) {
                $remoteInput[] = $input;
            }
            unset($job['input'][$key]);
        }

        $job['input'] = $remoteInput;

        if ($withUpload) {
            $job['process'] = false;
        }

        $job = $this->responseToArray(
            $this->client->sendRequest(Resources::JOB, OnlineConvertClient::METHOD_POST, $job)
        );

        if ($withUpload) {
            $job = $this->waitStatus($job['id'], self::STATUS_READY);

            foreach ($uploadInput as $key => $input) {
                $this->postFile($input, $job);
            }

            $job = $this->waitStatus($job['id'], self::STATUS_READY);

            $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);

            $job['process'] = true;
            $job            = $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_PATCH, $job)
            );
        }

        if (!$this->async) {
            $this->waitStatus($job['id'], self::STATUS_COMPLETED);
        }

        return $this->getJob($job['id']);
    }

    /**
     * Loop to check if the job finish
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the requests
     *
     * @param string $jobId
     * @param string $waitTo      a value of STATUS_ const
     * @param int    $waitingTime time to wait between request in seconds
     *
     * @return array
     */
    public function waitStatus($jobId, $waitTo, $waitingTime = 1)
    {
        $response        = null;
        $url             = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $jobId]);
        $responseAsArray = [];

        $done = false;
        while (!$done) {
            sleep($waitingTime);
            $response        = $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET);
            $responseAsArray = $this->responseToArray($response);
            $status          = $responseAsArray['status']['code'];
            if ($status == $waitTo) {
                $done = true;
            } elseif ($status == self::STATUS_FAILED) {
                throw new JobFailedException(json_encode($response));
            }
        }

        return $responseAsArray;
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

        return $this->responseToArray($this->client->sendRequest($url, OnlineConvertClient::METHOD_GET));
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
        $this->client->sendRequest($url, OnlineConvertClient::METHOD_DELETE, []);

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

        return $this->responseToArray($this->client->sendRequest($url, OnlineConvertClient::METHOD_GET));
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

        return $this->responseToArray($this->client->sendRequest($url, OnlineConvertClient::METHOD_GET));
    }

    /**
     * Start to process a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param array $job if this is not defined will take the last one created
     *
     * @return array
     */
    public function processJob(array $job)
    {
        $job['process'] = true;

        $this->waitStatus($job['id'], self::STATUS_READY);

        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);
        $this->client->sendRequest($url, OnlineConvertClient::METHOD_PATCH, $job);

        $url = $this->client->generateUrl(Resources::JOB_ID, ['job_id' => $job['id']]);
        $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET);

        if (!$this->async) {
            $this->waitStatus($job['id'], self::STATUS_COMPLETED);
        }

        return $this->responseToArray($this->client->sendRequest($url, OnlineConvertClient::METHOD_GET));
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
            $this->client->sendRequest(Resources::JOB, OnlineConvertClient::METHOD_POST, $job)
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
    private function checkJobCallbackForAsync(array $job)
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
}
