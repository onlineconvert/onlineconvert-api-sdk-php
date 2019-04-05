<?php

namespace OnlineConvert;

use OnlineConvert\Api;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Configuration;
use OnlineConvert\Endpoint\InputEndpoint;
use OnlineConvert\Endpoint\JobsEndpoint;
use OnlineConvert\Endpoint\OutputEndpoint;
use OnlineConvert\Model\JobStatus;
use OnlineConvert\Client\Interfaced;
use UnexpectedValueException;

/**
 * Provide a simple way to use the SDK
 *
 * @package OnlineConvert
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
     * @var OutputEndpoint
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
     * @var array
     */
    private $inputs = [];

    /**
     * @var array
     */
    private $conversions = [];

    /**
     * @var string
     */
    private $status = '';

    /**
     * SimpleJob constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->config = new Configuration();
        $this->config->setApiKey('main', $apiKey);
        $this->config->setDownloadFolder('./tests/Functional/downloads/');
    }

    /**
     * Add a url to the job
     *
     * @param string $url
     *
     * @return SimpleJob
     *
     * @throws UnexpectedValueException when error on the request
     */
    public function download($url)
    {
        if (empty($url)) {
            throw new UnexpectedValueException('Url provided is empty');
        }

        $input = [
            'type'   => InputEndpoint::INPUT_TYPE_REMOTE,
            'source' => $url,
        ];

        $this->inputs[] = $input;

        return $this;
    }

    /**
     *  Add a file to the job
     *
     * @param string $file
     *
     * @return SimpleJob
     *
     * @throws UnexpectedValueException when error on the request
     */
    public function upload($file)
    {
        if (empty($file)) {
            throw new UnexpectedValueException('No File To Upload');
        }

        $input = [
            'type'   => InputEndpoint::INPUT_TYPE_UPLOAD,
            'source' => $file,
        ];

        $this->inputs[] = $input;

        return $this;
    }

    /**
     * Start the Job
     *
     * @return SimpleJob
     */
    public function start()
    {
        $this->client         = new OnlineConvertClient($this->config, 'main');
        $this->api            = new Api($this->client);
        $this->outputEndpoint = $this->api->getOutputEndpoint();

        $syncJob = [
            'input'      => $this->inputs,
            'conversion' => $this->conversions,
        ];

        $this->job = $this->api->postFullJob($syncJob)->getJobCreated();

        return $this;
    }

    /**
     * Adds a new conversion to the job
     *
     * @param string $format
     * @param array  $options
     *
     * @return SimpleJob
     *
     * @throws UnexpectedValueException when target is empty
     */

    public function addConversion($format, $options)
    {
        if (empty($format)) {
            throw new UnexpectedValueException('Format is Empty');
        }

        $this->conversions [] = [
            'target'  => $format,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Wait until status is 'completed'
     *
     * @return SimpleJob
     */
    public function wait()
    {
        $status = [
            JobStatus::STATUS_COMPLETED,
        ];

        $JobsEndpoint = new JobsEndpoint($this->client);
        $response     = $JobsEndpoint->waitStatus($this->job['id'], $status);
        $this->status = $response['status']['code'];

        return $this;
    }

    /**
     * Save the converted files to the download folder
     *
     * @param string $path
     *
     * @return array $job
     */
    public function saveTo($path)
    {
        $this->config->downloadFolder = $path;
        $this->outputEndpoint->downloadOutputs($this->job);

        return $this->job;
    }
}

