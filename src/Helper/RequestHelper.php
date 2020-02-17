<?php

namespace OnlineConvert\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\HTTPMethodNotAllowed;
use OnlineConvert\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

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
     * @param array      $customOptions
     *
     * @return string
     */
    public function sendRequest(
        $url,
        $method,
        array $defaultHeader,
        Client $client,
        array $postData = null,
        array $customOptions = []
    ) {
        $options = [
            'headers' => $defaultHeader,
        ];

        $options = array_merge($customOptions, $options);

        if ($method == 'POST' || $method == 'PATCH' || $method == 'DELETE') {
            $postData        = json_encode($postData);
            $options['body'] = $postData;

            try {
                $request = $this->spinRequestHelper->doSpinRequest(
                    $method,
                    $url,
                    $options,
                    0,
                    $client
                );
            } catch (GuzzleRequestException $e) {
                $requestException = new RequestException($e->getMessage());

                $response = $e->getResponse();

                if ($response instanceof ResponseInterface) {
                    $requestException->setResponse($response);
                }

                throw $requestException;
            }
        } else {
            try {
                $request = $this->spinRequestHelper->doSpinRequest(
                    $method,
                    $url,
                    $options,
                    0,
                    $client
                );
            } catch (GuzzleRequestException $e) {
                $requestException = new RequestException($e->getMessage());

                $response = $e->getResponse();

                if ($response instanceof ResponseInterface) {
                    $requestException->setResponse($response);
                }

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
     * @param array       $customOptions
     *
     * @return string
     */
    public function postLocalFile(
        $source,
        $url,
        $defaultHeader,
        Client $client,
        $token = null,
        array $customOptions = []
    ) {
        if ($token) {
            $defaultHeader[OnlineConvertClient::HEADER_OC_JOB_TOKEN] = $token;
        }

        $options = [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => $this->fileSystemHelper->fopen($source, 'r'),
                ],
            ],
            'headers'   => $defaultHeader,
        ];

        $options = array_merge($customOptions, $options);

        try {
            $request = $this->spinRequestHelper->doSpinRequest(
                'POST',
                $url,
                $options,
                0,
                $client
            );
        } catch (GuzzleRequestException $e) {
            $requestException = new RequestException($e->getMessage());

            $response = $e->getResponse();

            if ($response instanceof ResponseInterface) {
                $requestException->setResponse($response);
            }

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
