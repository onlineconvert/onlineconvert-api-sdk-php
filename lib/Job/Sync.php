<?php

namespace Qaamgo\Job;

use Qaamgo\ApiClient;
use Qaamgo\ApiException;
use Qaamgo\Configuration;
use Qaamgo\Helper\JsonPersister;
use Qaamgo\JobsApi;
use Qaamgo\Models\Conversion;
use Qaamgo\Models\InputFile;
use Qaamgo\Models\Job;
use Qaamgo\Output;
use Qaamgo\Validator\Options;
use Qaamgo\Models\Status;
use Qaamgo\Helper\Common;

/**
 * Class JobCreator
 * @package SwaggerClient
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Sync implements Interfaced
{
    protected $client;

    protected $apiKey;

    protected $inputFiles = [];

    protected $schemaPersister;

    protected $optionsValidator;

    protected $job;

    protected $jobsApi;

    protected $conversion;

    protected $createdJob;

    public function __construct($https = false, $host = null, $apiKey)
    {
        $this->client = new ApiClient($https, $host);
        $this->apiKey = $apiKey;
        $this->schemaPersister = new JsonPersister();
        $this->optionsValidator = new Options();
        $this->job = new Job();
        $this->jobsApi = new JobsApi($this->client);
        $this->conversion = new Conversion();
        $this->output = new Output($this->apiKey);
    }

    /**
     * Create a job which process finish when the coversion is completed or fails
     *
     * @param $category
     * @param $target
     * @param $input
     * @param array $options
     * @return mixed
     * @throws ApiException
     */
    public function createSyncJob($category, $target, $input, $options = [])
    {
        $inputFile = new InputFile();
        $inputFile->source = $input;

        $this->filterInput($inputFile, $input);

        $this->conversion->category = $category;
        $this->conversion->target = $target;

        $this->validateOptions($options, $this->conversion->category, $this->conversion->target);

        $this->createJobForApi();

        $this->lookStatus($this->createdJob->id);

        return $this->getJobInfo($this->createdJob->id);
    }

    /**
     * Create a job and post the file if is needed
     */
    protected function createJobForApi()
    {
        $this->job->conversion[] = $this->conversion;
        $this->createdJob = $this->jobsApi->jobsPost($this->apiKey, $this->job);
        if (count($this->inputFiles) > 0) {
            $this->postFile($this->inputFiles[0]);
        }
    }

    /**
     * Post file to the url that is assigned to the job
     *
     * @param InputFile $file
     * @return bool
     */
    protected function postFile(InputFile $file)
    {
        $this->createdJob->server = Common::httpsToHttpVice($this->createdJob->server);
        $this->createdJob->input[] = $this->jobsApi->jobsPostFile(
            $this->apiKey,
            $this->createdJob,
            $file->source
        );
        return true;
    }


    /**
     * Call the job for check the status is completed
     *
     * @param $jobId
     * @return Array|Status|String
     * @throws ApiException
     */
    protected function lookStatus($jobId)
    {
        /** @var Status $status */
        $status = new Status();
        while ($status->code != Configuration::STATUS_COMPLETED) {
            $status = $this->getStatus($jobId);
            if ($status->code == Configuration::STATUS_FAILED) {
                throw new ApiException(
                    'Job Status: ' . Configuration::STATUS_FAILED . 'Message: ' . $status->info
                );
            }
            if ($status->code == Configuration::STATUS_INVALID) {
                throw new ApiException(
                    'Job Status: ' . Configuration::STATUS_INVALID . 'Message: ' . $status->info
                );
            }
            if ($status->code == Configuration::STATUS_INCOMPLETE) {
                throw new ApiException(
                    'Job Status: ' . Configuration::STATUS_INCOMPLETE . 'Message: ' . $status->info
                );
            }
        }
        return $status;
    }

    /**
     * @param $jobId
     * @return Status
     */
    public function getStatus($jobId)
    {
        return $this->jobsApi->jobsJobIdGet($this->apiKey, $jobId)->status;
    }

    /**
     * @param $jobId
     * @return Job
     */
    public function getJobInfo($jobId)
    {
        return $this->jobsApi->jobsJobIdGet($this->apiKey, $jobId);
    }

    /**
     * Set the type of the source to convert
     *
     * @param InputFile $inputFile
     * @param $input
     * @return InputFile
     */
    protected function filterInput(InputFile $inputFile, $input)
    {
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $inputFile->type = Configuration::INPUT_REMOTE;
            $this->job->input[] = $inputFile;
        } else {
            $inputFile->type = Configuration::INPUT_UPLOAD;
            $this->inputFiles[0] = $inputFile;
        }
        return $inputFile;
    }

    /**
     * Validate the options of the conversion whit a json schema
     * @param $options
     * @param $category
     * @param $target
     */
    protected function validateOptions($options, $category, $target)
    {
        if (empty($options)) {
            return;
        }

        $schema = $this->schemaPersister->getOptionsSchema($category, $target);
        $this->optionsValidator->validate($options, $schema);
        $this->conversion->options = $options;
    }

    public function getOutput($jobId)
    {
        return $this->output->getJobOutput($jobId);
    }
}
