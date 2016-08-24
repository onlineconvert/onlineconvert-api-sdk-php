<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\OnlineConvertSdkException;

/**
 * Manage Conversion Endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class ConversionEndpoint extends Abstracted
{

    /**
     * Post a new conversion
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param array $conversion
     * @param array $job
     *
     * @return array
     */
    public function postJobConversion(array $conversion, array $job)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_CONVERSIONS, ['job_id' => $job['id']]);

        return $this->responseToArray(
            $this->client->sendRequest($url, OnlineConvertClient::METHOD_POST, $conversion)
        );
    }

    /**
     * Get all the conversions from a job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobConversions($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_CONVERSIONS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
        );
    }

    /**
     * Get conversion from the given id
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     * @param string $conversionId
     *
     * @return array
     */
    public function getConversion($jobId, $conversionId)
    {
        $url = $this->client->generateUrl(
            Resources::JOB_ID_CONVERSION_ID,
            ['job_id' => $jobId, 'conversion_id' => $conversionId]
        );

        return $this->responseToArray($this->client->sendRequest($url, Interfaced::METHOD_GET));
    }

    /**
     * Delete conversion from the given id
     *
     * @api
     *
     * @throws OnlineConvertSdkException when the deletion was not successfully - request error
     *
     * @param string $jobId
     * @param string $conversionId
     *
     * @return bool TRUE when the deletion was successfully
     */
    public function deleteConversion($jobId, $conversionId)
    {
        $url = $this->client->generateUrl(
            Resources::JOB_ID_CONVERSION_ID,
            ['job_id' => $jobId, 'conversion_id' => $conversionId]
        );

        $this->client->sendRequest($url, OnlineConvertClient::METHOD_DELETE, []);

        return true;
    }
}
