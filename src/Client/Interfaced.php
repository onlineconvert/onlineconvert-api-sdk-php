<?php

namespace OnlineConvert\Client;

use OnlineConvert\Configuration;
use OnlineConvert\Exception\RequestException;

/**
 * This class is a interface be implemented by the client class
 *
 * @package OnlineConvert\Client
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
 */
interface Interfaced
{
    /**
     * @const string
     */
    const METHOD_GET = 'GET';

    /**
     * @const string
     */
    const METHOD_POST = 'POST';

    /**
     * @const string
     */
    const METHOD_PATCH = 'PATCH';

    /**
     * @const string
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @const string
     */
    const HEADER_OC_SDK_CLIENT_VERSION = 'X-OC-SDK-CLIENT';

    /**
     * @const string
     */
    const HEADER_OC_API_KEY = 'X-OC-API-KEY';

    /**
     * @const string
     */
    const HEADER_OC_JOB_TOKEN = 'X-OC-TOKEN';

    /**
     * @const string
     */
    const CLIENT_USER_AGENT = 'OnlineConvert API2 SDKv2 Client';

    /**
     * Send request to the specified url
     *
     * @throws RequestException when the request fails
     *
     * @param string     $url
     * @param string     $method
     * @param array|null $postData
     * @param array|null $headers
     *
     * @return string json response
     */
    public function sendRequest($url, $method, array $postData = null, array $headers = []);

    /**
     * Post a file directly to a url using token
     * You can use this method as shortcut to not add too much complexity to the sendRequest() method
     *
     * @throws RequestException when the request fails
     *
     * @param string      $source
     * @param string      $url
     * @param string|null $token
     *
     * @return string json response
     */
    public function postLocalFile($source, $url, $token = null);


    /**
     * Generate url replacing parameter into the string and adding the query parameters
     *
     * @param string $resource
     * @param array  $parameters
     * @param array  $query
     *
     * @return string
     */
    public function generateUrl($resource, $parameters = [], $query = []);

    /**
     * Get the client instance used in the background
     *
     * @return mixed
     */
    public function getClient();

    /**
     * Get the configuration instance used in this client
     *
     * @return Configuration
     */
    public function getConfig();

    /**
     * Set a predefined header
     *
     * @param string         $headerKey
     * @param string|integer $value
     *
     * @return void
     */
    public function setHeader($headerKey, $value);

    /**
     * Get a a header value by key
     *
     * @param string $headerKey
     *
     * @return string|false False when is the key given has not been configured
     */
    public function getHeader($headerKey);
}
