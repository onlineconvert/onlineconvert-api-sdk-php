<?php
namespace OnlineConvert;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Endpoint\ConversionEndpoint;
use OnlineConvert\Endpoint\EndpointFactory;
use OnlineConvert\Endpoint\InformationEndpoint;
use OnlineConvert\Endpoint\InputEndpoint;
use OnlineConvert\Endpoint\JobsEndpoint;
use OnlineConvert\Endpoint\OutputEndpoint;

/**
 * Provide some shortcuts to the endpoints and methods
 *
 * @package OnlineConvert
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Api
{
    /**
     * @var Interfaced
     */
    private $client;

    /**
     * @var array
     */
    private $job;

    /**
     * Determine if this API Handler will be async or sync
     *
     * @var bool
     */
    private $async;

    /**
     * Endpoints to create at initialize $this
     *
     * @var array
     */
    private $endpoints = [
        'conversion',
        'input',
        'jobs',
        'output',
        'information',
    ];

    /**
     * @var ConversionEndpoint
     */
    private $conversionEndpoint;

    /**
     * @var InputEndpoint
     */
    private $inputEndpoint;

    /**
     * @var JobsEndpoint
     */
    private $jobsEndpoint;

    /**
     * @var OutputEndpoint
     */
    private $outputEndpoint;

    /**
     * @var InformationEndpoint
     */
    private $informationEndpoint;

    /**
     * @var EndpointFactory
     */
    private $endpointFactory;

    /**
     * Api constructor.
     *
     * @param Interfaced $client
     * @param bool       $async
     */
    public function __construct(Interfaced $client, $async = false)
    {
        $this->client          = $client;
        $this->async           = $async;
        $this->endpointFactory = new EndpointFactory($this->client);
        $this->createEndpoints();
    }

    private function createEndpoints()
    {
        foreach ($this->endpoints as $endpoint) {
            $endpointProperty        = $endpoint . 'Endpoint';
            $this->$endpointProperty = $this->endpointFactory->getEndpoint($endpoint);

            if ($this->$endpointProperty instanceof JobsEndpoint) {
                $this->$endpointProperty->setAsync($this->async);
            }
        }
    }

    /**
     * @return ConversionEndpoint
     */
    public function getConversionEndpoint()
    {
        return $this->conversionEndpoint;
    }

    /**
     * @return InputEndpoint
     */
    public function getInputEndpoint()
    {
        return $this->inputEndpoint;
    }

    /**
     * @return JobsEndpoint
     */
    public function getJobsEndpoint()
    {
        return $this->jobsEndpoint;
    }

    /**
     * @return OutputEndpoint
     */
    public function getOutputEndpoint()
    {
        return $this->outputEndpoint;
    }

    /**
     * @return InformationEndpoint
     */
    public function getInformationEndpoint()
    {
        return $this->informationEndpoint;
    }

    /**
     * Post full job
     *
     * @see \OnlineConvert\Endpoint\JobsEndpoint::postFullJob()
     *
     * @param array $job
     *
     * @return $this
     */
    public function postFullJob(array $job)
    {
        $this->job = $this->jobsEndpoint->postFullJob($job);

        return $this;
    }

    /**
     * Get the created job
     *
     * @see \OnlineConvert\Api::postFullJob
     *
     * @return array
     */
    public function getJobCreated()
    {
        return $this->job;
    }

    /**
     * Get job information by the given id
     *
     * @see \OnlineConvert\Endpoint\JobsEndpoint::getJob
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJob($jobId)
    {
        return $this->jobsEndpoint->getJob($jobId);
    }

    /**
     * Get outputs from job by the given job id
     *
     * @see \OnlineConvert\Endpoint\OutputEndpoint::getJobOutputs
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobOutputs($jobId)
    {
        return $this->outputEndpoint->getJobOutputs($jobId);
    }

    /**
     * Get the information from a target conversion
     *
     * @see \OnlineConvert\Endpoint\InformationEndpoint::getConversionSchema
     *
     * @param string      $target
     * @param string|null $category
     *
     * @return array
     */
    public function getConversionInfo($target, $category = null)
    {
        return $this->informationEndpoint->getConversionSchema($target, $category);
    }

    /**
     * @return Interfaced
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Interfaced $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
