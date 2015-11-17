<?php

namespace Qaamgo;

use Qaamgo\Helper\JsonPersister;
use Qaamgo\Models\Conversion;
use Qaamgo\Models\InputFile;
use Qaamgo\Models\Job;
use Qaamgo\Validator\Options;
use Qaamgo\Models\Status;

/**
 * Class JobCreator
 * @package SwaggerClient
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class JobCreator
{
    const INPUT_REMOTE = 'remote';

    const INPUT_UPLOAD = 'upload';

    private $client;

    private $apiKey;

    private $inputFiles = [];

    private $schemaPersister;

    private $optionsValidator;

    private $job;

    private $jobsApi;

    private $conversion;

    private $createdJob;

    public function __construct($https = false, $host = null, $apiKey)
    {
        $this->client = new ApiClient($https, $host);
        $this->apiKey = $apiKey;
        $this->schemaPersister = new JsonPersister();
        $this->optionsValidator = new Options();
        $this->job = new Job();
        $this->jobsApi = new JobsApi($this->client);
        $this->conversion = new Conversion();
    }

    /**
     * @param $category
     * @param $target
     * @param $input
     * @param array $options
     * @throws Validator\NoValidOptionsException
     */
    public function createJob($category, $target, $input, $options = [])
    {
        $inputFile = new InputFile();
        $inputFile->source = $input;

        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $inputFile->type = Constants::INPUT_REMOTE;
            $this->job->input[] = $inputFile;
        } else {
            $inputFile->type = Constants::INPUT_UPLOAD;
            $this->inputFiles[0] = $inputFile;
        }

        $this->conversion->category = $category;
        $this->conversion->target = $target;

        if (!empty($options)) {
            $schema = $this->schemaPersister->getOptionsSchema($category, $target);
            $this->optionsValidator->validate($options, $schema);
            $this->conversion->options = $options;
        }
    }

    /**
     * Create a job and post the file if is needed
     */
    private function createJobForApi()
    {
        $this->job->conversion[] = $this->conversion;
        //Expected all the inputs with the same type
        $this->createdJob = $this->jobsApi->jobsPost($this->apiKey, $this->job);
        if (count($this->inputFiles) > 0) {
            $this->postFile($this->inputFiles[0]);
        }
    }

    /**
     * @param InputFile $file
     * @return bool
     */
    private function postFile(InputFile $file)
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
     * @return Status|String|Array
     */
    public function lookStatus()
    {
        /** @var Status $status */
        $status = new Status();
        while ($status->code != Constants::STATUS_COMPLETED) {
            $status = $this->getStatus();
            if ($status->code == Constants::STATUS_FAILED) {
                throw new ConversionException(
                    'Job Status: ' . Constants::STATUS_FAILED . 'Message: ' . $status->info
                );
            }
            if ($status->code == Constants::STATUS_INVALID) {
                throw new ConversionException(
                    'Job Status: ' . Constants::STATUS_INVALID . 'Message: ' . $status->info
                );
            }
            if ($status->code == Constants::STATUS_INCOMPLETE) {
                throw new ConversionException(
                    'Job Status: ' . Constants::STATUS_INCOMPLETE . 'Message: ' . $status->info
                );
            }
        }
        return $status;
    }

    /**
     * @return Status
     */
    public function getStatus($jobId)
    {
        return $this->jobsApi->jobsJobIdGet($this->apiKey, $jobId)->status;
    }
}
