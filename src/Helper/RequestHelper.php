<?php

namespace OnlineConvert\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\HTTPMethodNotAllowed;
use OnlineConvert\Exception\RequestException;

/**
 * Class RequestHelper
 *
 * @package OnlineConvert\Helper
 */
class RequestHelper
{

    /**
     * @var SpinRequestHelper
     */
    private $spinRequestHelper;

    /**
     * @var FileSystemHelper
     */
    private $fileSystemHelper;

    /**
     * Allowed method in Online Convert API
     *
     * @var array
     */
    private $allowedMethods = [
        OnlineConvertClient::METHOD_GET,
        OnlineConvertClient::METHOD_POST,
        OnlineConvertClient::METHOD_PATCH,
        OnlineConvertClient::METHOD_DELETE,
    ];

    /**
     * RequestHelper constructor.
     *
     * @param SpinRequestHelper $spinRequestHelper
     * @param FileSystemHelper  $fileSystemHelper
     */
    public function __construct(SpinRequestHelper $spinRequestHelper, FileSystemHelper $fileSystemHelper)
    {
        $this->spinRequestHelper = $spinRequestHelper;
        $this->fileSystemHelper  = $fileSystemHelper;
    }

    /**
     * Sends a request
     *
     * @param string     $url
     * @param string     $method
     * @param array      $defaultHeader
     * @param Client     $client
     * @param array|null $postData
     *
     * @return string
     */
    public function sendRequest($url, $method, array $defaultHeader, Client $client, array $postData = null)
    {
        if ($method == 'POST' || $method == 'PATCH' || $method == 'DELETE') {
            $postData = json_encode($postData);

            try {
                $request = $this->spinRequestHelper->doSpinRequest(
                    $method,
                    $url,
                    [
                        'body'    => $postData,
                        'headers' => $defaultHeader,
                    ],
                    0,
                    $client
                );
            } catch (GuzzleRequestException $e) {
                $requestException = new RequestException($e->getMessage());
                $requestException->setResponse($e->getResponse());

                throw $requestException;
            }
        } else {
            try {
                $request = $this->spinRequestHelper->doSpinRequest(
                    $method,
                    $url,
                    ['headers' => $defaultHeader],
                    0,
                    $client
                );
            } catch (GuzzleRequestException $e) {
                $requestException = new RequestException($e->getMessage());
                $requestException->setResponse($e->getResponse());

                throw $requestException;
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
     * Posts a local file
     *
     * @param string      $source
     * @param string      $url
     * @param array       $defaultHeader
     * @param Client      $client
     * @param string|null $token
     *
     * @return string
     */
    public function postLocalFile($source, $url, $defaultHeader, Client $client, $token = null)
    {
        if ($token) {
            $defaultHeader[OnlineConvertClient::HEADER_OC_JOB_TOKEN] = $token;
        }

        try {
            $request = $this->spinRequestHelper->doSpinRequest(
                'POST',
                $url,
                [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => $this->fileSystemHelper->fopen($source, 'r'),
                        ],
                    ],
                    'headers'   => $defaultHeader,
                ],
                0,
                $client
            );
        } catch (GuzzleRequestException $e) {
            $requestException = new RequestException($e->getMessage());
            $requestException->setResponse($e->getResponse());

            throw $requestException;
        }

        return $request->getBody()->getContents();
    }

    /**
     * Check if the method is allowed
     *
     * @throws HTTPMethodNotAllowed when the method given is not allowed
     *
     * @param string $method
     */
    public function checkMethodToSendRequest($method)
    {
        if (!in_array($method, $this->allowedMethods)) {
            throw new HTTPMethodNotAllowed($method . ' is not allowed');
        }
    }
}
