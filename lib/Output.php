<?php

namespace Qaamgo;

use Qaamgo\Models\OutputFile;

class Output
{
    private $client;
    private $apiKey;
    private $outputManager;

    public function __construct($apiKey, $https = false, $host = null)
    {
        $this->client = new ApiClient($https, $host);
        $this->apiKey = $apiKey;
        $this->outputManager = new OutputApi($this->client);
    }

    /**
     * @param $jobId
     * @return OutputFile[]
     */
    public function getJobOutput($jobId)
    {
        return $this->outputManager->jobsJobIdOutputGet($this->apiKey, $jobId);
    }
}