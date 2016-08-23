<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\OnlineConvertSdkException;

/**
 * Manage Information endpoint
 * Provide some extra methods to get information about the api
 *
 * @package OnlineConvert\Endpoint
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class InformationEndpoint extends Abstracted
{
    /**
     * Get API schema
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->responseToArray(
            $this->client->sendRequest(Resources::GET_SCHEMA, OnlineConvertClient::METHOD_GET)
        );
    }

    /**
     * Get list of valid statuses in api
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function getStatusesList()
    {
        return $this->responseToArray(
            $this->client->sendRequest(Resources::STATUSES, OnlineConvertClient::METHOD_GET)
        );
    }

    /**
     * Get list of all the possibles conversions
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function getConversionsList()
    {
        return $this->responseToArray(
            $this->client->sendRequest(Resources::CONVERSIONS, OnlineConvertClient::METHOD_GET)
        );
    }

    /**
     * Return schema for a specific conversion
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string      $target
     * @param null|string $category
     *
     * @return array
     */
    public function getConversionSchema($target, $category = null)
    {
        $query['target'] = $target;

        if ($category) {
            $query['category'] = $category;
        }

        $url = $this->client->generateUrl(Resources::CONVERSIONS, [], $query);

        return $this->responseToArray($this->client->sendRequest($url, OnlineConvertClient::METHOD_GET))[0];
    }
}
