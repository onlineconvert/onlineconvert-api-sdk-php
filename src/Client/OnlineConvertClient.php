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
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class OnlineConvertClient implements Interfaced
{

    /**
     * User agent name
     *
     * @var string user agent
     */
    protected $userAgent = "OnlineConvert API2 SDKv2 Client";

    /**
     * Default header for the client
     *
     * @var array
     */
    protected $defaultHeader = [
        'X-OC-SDK-CLIENT' => 2,
        'X-OC-API-KEY'    => null,
        'X-OC-TOKEN'      => null,
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
     */
    public function __construct(Configuration $configuration, $apiKeyPrefix)
    {
        $this->config = $configuration;

        if ($this->config->https) {
            $this->host       = Resources::HTTPS_HOST;
            $config['verify'] = false;
        } else {
            $this->host = Resources::HTTP_HOST;
        }

        $this->apiKey                        = $this->config->getApiKey($apiKeyPrefix);
        $this->defaultHeader['X-OC-API-KEY'] = $this->apiKey;

        $config['base_uri'] = $this->host;
        $config['headers']  = $this->defaultHeader;

        $this->client = new Client($config);
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest($url, $method, array $postData = null, array $headers = null)
    {
        $method = strtoupper($method);

        $this->checkMethodToSendRequest($method);

        $this->mergeHeaders($headers);

        if ($method == 'POST' || $method == 'PATCH') {
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

        return $request->getBody()->getContents();
    }

    /**
     * {@inheritDoc}
     */
    public function postLocalFile($source, $url, $token = null)
    {
        $this->defaultHeader['X-OC-TOKEN'] = $token;

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
            $this->defaultHeader = array_merge($this->defaultHeader, $headers);
        }
    }
}
