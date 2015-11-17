<?php

namespace Qaamgo;

use Qaamgo\Helper\JsonPersister;
use Qaamgo\Models\Conversion;
use Qaamgo\Models\InputFile;
use Qaamgo\Models\Job;
use Qaamgo\Validator\Options;
use Qaamgo\Models\Status;
use Qaamgo\Helper\Common;

/**
 * Class JobCreator
 * @package SwaggerClient
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class JobCreator
{
    const INPUT_REMOTE = 'remote';

    const INPUT_UPLOAD = 'upload';

    const STATUS_COMPLETED = 'completed';

    const STATUS_QUEUED = 'queued';

    const STATUS_DOWNLOADING = 'downloading';

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_FAILED = 'failed';

    const STATUS_INVALID = 'invalid';

    const STATUS_INCOMPLETE = 'incomplete';

    const STATUS_READY = 'ready';

    private $client;

    private $apiKey;

    private $inputFiles = [];

    private $schemaPersister;

    private $optionsValidator;

    private $job;

    private $jobsApi;

    private $conversion;

    private $createdJob;

    private $synchronous;

    public function __construct($https = false, $host = null, $apiKey, $synchronous = true)
    {
        $this->client = new ApiClient($https, $host);
        $this->apiKey = $apiKey;
        $this->schemaPersister = new JsonPersister();
        $this->optionsValidator = new Options();
        $this->job = new Job();
        $this->jobsApi = new JobsApi($this->client);
        $this->conversion = new Conversion();
        $this->synchronous = $synchronous;
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
            $inputFile->type = self::INPUT_REMOTE;
            $this->job->input[] = $inputFile;
        } else {
            $inputFile->type = self::INPUT_UPLOAD;
            $this->inputFiles[0] = $inputFile;
        }

        $this->conversion->category = $category;
        $this->conversion->target = $target;

        if (!empty($options)) {
            $schema = $this->schemaPersister->getOptionsSchema($category, $target);
            $this->optionsValidator->validate($options, $schema);
            $this->conversion->options = $options;
        }

        $this->createJobForApi();

        if ($this->synchronous) {
            $this->lookStatus($this->createdJob->id);
        }

        return $this->getJobInfo($this->createdJob->id);
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
    public function lookStatus($jobId)
    {
        /** @var Status $status */
        $status = new Status();
        while ($status->code != self::STATUS_COMPLETED) {
            $status = $this->getStatus($jobId);
            if ($status->code == self::STATUS_FAILED) {
                throw new ConversionException(
                    'Job Status: ' . self::STATUS_FAILED . 'Message: ' . $status->info
                );
            }
            if ($status->code == self::STATUS_INVALID) {
                throw new ConversionException(
                    'Job Status: ' . self::STATUS_INVALID . 'Message: ' . $status->info
                );
            }
            if ($status->code == self::STATUS_INCOMPLETE) {
                throw new ConversionException(
                    'Job Status: ' . self::STATUS_INCOMPLETE . 'Message: ' . $status->info
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


    /**
     * @param $jobId
     * @return mixed
     */
    public function getJobInfo($jobId)
    {
        return $this->jobsApi->jobsJobIdGet($this->apiKey, $jobId);
    }
}
