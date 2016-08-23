<?php
namespace OnlineConvert;

use OnlineConvert\Exception\NoApiKeyDefined;

/**
 * Global configuration for the Api Worker
 *
 * @package OnlineConvert
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Configuration
{
    /**
     * Array with our api keys.
     * Associative array where the key is the prefix of the api key and the value is the real api key
     *
     * @var array
     */
    private $apiKey = [];

    /**
     * Connect to the api via https
     *
     * @var bool
     */
    public $https = false;

    /**
     * Folder where the downloads of the outputs will be saved
     *
     * @var string
     */
    public $downloadFolder = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'downloads';

    /**
     * get api key by prefix
     *
     * @return string
     */
    public function getApiKey($prefix)
    {
        if (!isset($this->apiKey[$prefix])) {
            throw new NoApiKeyDefined("'$prefix' have to be defined");
        }

        $apiKey = trim($this->apiKey[$prefix]);

        if (empty($apiKey)) {
            throw new NoApiKeyDefined("'$prefix' have a empty string as api key");
        }

        return $apiKey;
    }

    /**
     * Get api keys
     *
     * @return array
     */
    public function getApiKeys()
    {
        return $this->apiKey;
    }


    /**
     * Set an api key
     *
     * @param string $prefix
     * @param string $apiKey
     */
    public function setApiKey($prefix, $apiKey)
    {
        $this->apiKey[$prefix] = $apiKey;
    }
}
