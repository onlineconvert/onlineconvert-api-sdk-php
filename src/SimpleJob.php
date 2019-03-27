<?php

namespace OnlineConvert;

use OnlineConvert\Api;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Configuration;
use OnlineConvert\Endpoint\InputEndpoint;
use OnlineConvert\Endpoint\JobsEndpoint;
use OnlineConvert\Exception\NotExistsExcption;
use OnlineConvert\Model\JobStatus;
use OnlineConvert\Client\Interfaced;

/**
 * Provide a simple way to use te SDK
 *
 * @package OnlineConvert
 *
 */
class SimpleJob
{

    /**
     * @var Configuration
     */
    private $config;
    /**
     * @var Interfaced
     */
    private $client;
    /**
     * @var Api
     */
    private $api;
    /**
     * @var object
     */
    private $outputEndpoint;
    /**
     * @var array
     */
    private $job;

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $jobId;
    /**
     * @var array
     */
    private $download = [];

    /**
     * @var array
     */
    private $options = [];
    /**
     * @var string
     */
    private $target = '';
    /**
     * @var string
     */
    private $status = '';
    /**
     * @var array
     */
    private $JobsEndpoint;
    /**
     * SimpleJob constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->config = new Configuration();
        $this->config->setApiKey('main', $this->apiKey);

    }

    /**
     * push a download url into the config array
     *
     * @param string $url
     * @throws NotExistsExcption when error on the request
     * @return array
     */
    public function download($url)
    {
        if (empty($url)) {
            throw new NotExistsExcption('Url provided is empty');
        }
        $input = [
            'type' => InputEndpoint::INPUT_TYPE_REMOTE,
            'source' => $url,
        ];
        array_push($this->download, $input);
        return $this;
    }

    /**
     *setTarget
     *
     * @param string $target
     * @throws NotExistsExcption when target is empty.
     * @return string $target
     */
    public function setTarget($target)
    {
        if (empty($target)) {
            throw new NotExistsExcption('Target is Empty');
        }
        $this->target = $target;
        return $this;
    }

    /**
     *  push a file  into the config array
     * @param string $file
     * @throws NotExistsExcption when error on the request
     * @return array
     */
    public function upload($file)
    {
        if (empty($file)) {
            throw new NotExistsExcption('No File To Upload');
        }
        $input = [
            'type' => InputEndpoint::INPUT_TYPE_UPLOAD,
            'source' => $file,
        ];
        array_push($this->download, $input);
        return $this;
    }

    /**
     * setOptions for conversion process
     *
     * @param array $options
     * @return array
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Initiate the Job
     *
     * @return array
     */
    public function start()
    {
        $this->client = new OnlineConvertClient($this->config, 'main');
        $this->api = new Api($this->client);
        $this->outputEndpoint = $this->api->getOutputEndpoint();

        $syncJob = [
            'input' => $this->download,
            'conversion' => [
                [
                    'target' => $this->target,
                    'options' => $this->options,
                ],
            ],
        ];

        $this->job = $this->api->postFullJob($syncJob)->getJobCreated();
        $this->jobId = $this->job['id'];
        return $this;
    }
    /**
     * wait
     *
     * @return string $status
     */
    public function wait()
    {
        $status = [
            JobStatus::STATUS_COMPLETED,
        ];
        $this->JobsEndpoint = new JobsEndpoint($this->client);
        $response = $this->JobsEndpoint->waitStatus($this->jobId, $status);
        $this->status = $response['status']['code'];
        return $this;
    }
    /**
     * Save the done file to the download path
     *
     * @param string $path
     * @return string output_id
     */
    public function saveTo($path)
    {
        $this->config->downloadFolder = $path;
        $this->outputEndpoint->downloadOutputs($this->job);
        return $this->job['output'][0]['id'];

    }

}
