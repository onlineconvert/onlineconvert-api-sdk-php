<?php
namespace OnlineConvert\Client;

use GuzzleHttp\Client;
use OnlineConvert\Configuration;
use OnlineConvert\Helper\FileSystemHelper;
use OnlineConvert\Helper\RequestHelper;
use OnlineConvert\Helper\SpinRequestHelper;

/**
 * Class OnlineConvertClient
 *
 * @package OnlineConvert\Client
 */
class OnlineConvertClient implements Interfaced
{
    /**
     * Default header for the client
     *
     * @var array
     */
    protected $defaultHeader = [
        'User-Agent'                       => self::CLIENT_USER_AGENT,
        self::HEADER_OC_SDK_CLIENT_VERSION => 2,
    ];

    /**
     * Configuration instance
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Guzzle client
     *
     * @var Client
     */
    protected $client;

    /**
     * @var RequestHelper
     */
    protected $requestHelper;

    /**
     * Real user api key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Host to send request depends if the https options in the configuration is set to true
     *
     * @see \OnlineConvert\Configuration::$https
     *
     * @var string
     */
    private $host;

    /**
     * OnlineConvertClient constructor.
     *
     * @param Configuration $configuration
     * @param               $apiKeyPrefix
     * @param null|string   $host
     */
    public function __construct(Configuration $configuration, $apiKeyPrefix = null, $host = null)
    {
        $this->config = $configuration;
        $config       = $configuration->getOptions();

        if ($this->config->https) {
            $this->host       = Resources::HTTPS_HOST;
            $config['verify'] = false;
        } else {
            $this->host = Resources::HTTP_HOST;
        }

        if ($host) {
            $this->host = $host;
        }

        if ($apiKeyPrefix) {
            $this->apiKey = $this->config->getApiKey($apiKeyPrefix);
            $this->setHeader(self::HEADER_OC_API_KEY, $this->apiKey);
        }

        $config['base_uri'] = $this->host;
        $config['headers']  = $this->defaultHeader;

        $this->client        = new Client($config);
        $spinRequestHelper   = new SpinRequestHelper();
        $fileSystemHelper    = new FileSystemHelper();
        $this->requestHelper = new RequestHelper($spinRequestHelper, $fileSystemHelper);
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest($url, $method, array $postData = null, array $headers = [])
    {
        $method = strtoupper($method);

        $this->requestHelper->checkMethodToSendRequest($method);

        $this->mergeHeaders($headers);

        return $this->requestHelper->sendRequest(
            $url,
            $method,
            $this->defaultHeader,
            $this->client,
            $postData,
            $this->config->getOptions()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function postLocalFile($source, $url, $token = null)
    {
        return $this->requestHelper->postLocalFile(
            $source,
            $url,
            $this->defaultHeader,
            $this->client,
            $token,
            $this->config->getOptions()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl($resource, $parameters = [], $query = [])
    {
        $resource = preg_replace_callback(
            '/\{(?P<parameter>\w+)\}/',
            function ($match) use ($parameters) {
                $parameter = $match['parameter'];
                if (!isset($parameters[$parameter])) {
                    return false;
                }

                return $parameters[$parameter];
            },
            $resource
        );

        return $resource . (empty($query) ? '' : '?' . http_build_query($query));
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function setHeader($headerKey, $value)
    {
        $this->defaultHeader[$headerKey] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($headerKey)
    {
        return isset($this->defaultHeader[$headerKey])
            ? $this->defaultHeader[$headerKey]
            : false;
    }

    /**
     * Merge headers is it is necessary
     *
     * @param array|null $headers
     */
    protected function mergeHeaders(array $headers = null)
    {
        if ($headers !== null) {
            $this->defaultHeader = array_filter($this->defaultHeader);
            $headers             = array_filter($headers);
            $this->defaultHeader = array_merge($this->defaultHeader, $headers);
        }
    }
}
