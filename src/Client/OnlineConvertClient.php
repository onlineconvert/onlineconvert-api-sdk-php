<?php
namespace OnlineConvert\Client;

use GuzzleHttp\Client;
use OnlineConvert\Configuration;
use OnlineConvert\Exception\HTTPMethodNotAllowed;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use OnlineConvert\Exception\RequestException;

/**
 * Class OnlineConvertClient
 *
 * @package OnlineConvert\Client
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
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
     * Real user api key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Allowed method in Online Convert API
     *
     * @var array
     */
    private $allowedMethods = [self::METHOD_GET, self::METHOD_POST, self::METHOD_PATCH, self::METHOD_DELETE];

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

        $this->client = new Client($config);
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest($url, $method, array $postData = null, array $headers = [])
    {
        $method = strtoupper($method);

        $this->checkMethodToSendRequest($method);

        $this->mergeHeaders($headers);

        if ($method == 'POST' || $method == 'PATCH' || $method == 'DELETE') {
            $postData = json_encode($postData);

            try {
                $request = $this->client->request(
                    $method,
                    $url,
                    [
                        'body'    => $postData,
                        'headers' => $this->defaultHeader,
                    ]
                );
            } catch (GuzzleRequestException $e) {
                throw new RequestException($e->getMessage());
            }
        } else {
            try {
                $request = $this->client->request($method, $url, ['headers' => $this->defaultHeader]);
            } catch (GuzzleRequestException $e) {
                throw new RequestException($e->getMessage());
            }
        }

        if (!in_array($request->getStatusCode(), [200, 201, 204, 301, 302])) {
            throw new RequestException(
                sprintf(
                    'Status code: %d, was not valid. Reason: %s',
                    $request->getStatusCode(),
                    $request->getBody()->getContents()
                )
            );
        }

        return $request->getBody()->getContents();
    }

    /**
     * {@inheritDoc}
     */
    public function postLocalFile($source, $url, $token = null)
    {
        if ($token) {
            $this->defaultHeader[self::HEADER_OC_JOB_TOKEN] = $token;
        }

        try {
            $request = $this->client->request(
                'POST',
                $url,
                [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => fopen($source, 'r'),
                        ],
                    ],
                    'headers'   => $this->defaultHeader,
                ]
            );
        } catch (GuzzleRequestException $e) {
            throw new RequestException($e->getMessage());
        }

        return $request->getBody()->getContents();
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
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
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
     * Check if the method is allowed
     *
     * @throws HTTPMethodNotAllowed when the method given is not allowed
     *
     * @param string $method
     */
    protected function checkMethodToSendRequest($method)
    {
        if (!in_array($method, $this->allowedMethods)) {
            throw new HTTPMethodNotAllowed($method . ' is not allowed');
        }
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
