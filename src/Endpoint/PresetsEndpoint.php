<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\OnlineConvertSdkException;

/**
 * Manage Statistics endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 */
class PresetsEndpoint extends Abstracted
{
    /**
     * Find presets by the given parameters
     *
     * @param string $filter
     * @param string $category
     * @param string $target
     *
     * @return array
     */
    public function findPresets($filter = '', $category = '', $target = '')
    {
        $queryArray = $this->buildQueryArray($filter, $category, $target);
        $url = $this->client->generateUrl(Resources::URL_PRESETS, [], $queryArray);
        try {
            return $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns the query parameters in an array
     *
     * @param string $filter
     * @param string $category
     * @param string $target
     *
     * @return array
     */
    private function buildQueryArray($filter, $category, $target)
    {
        $queryArray = [];

        if (!empty($filter)) {
            $queryArray['filter'] = $filter;
        }
        if (!empty($category)) {
            $queryArray['category'] = $category;
        }
        if (!empty($target)) {
            $queryArray['target'] = $target;
        }

        return $queryArray;
    }

    /**
     * Saves a new Preset
     *
     * @param string $name
     * @param string $target
     * @param array  $options
     *
     * @return array
     *
     * @throws OnlineConvertSdkException
     */
    public function savePreset($name, $target, array $options)
    {
        $url = $this->client->generateUrl(Resources::URL_PRESETS, ['presetdata']);
        try {
            return $this->responseToArray(
                $this->client->sendRequest(
                    $url,
                    OnlineConvertClient::METHOD_POST,
                    ['name' => $name, 'target' => $target, 'options' => $options]
                )
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
