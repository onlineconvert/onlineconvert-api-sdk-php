<?php

namespace OnlineConvert\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the client has request errors
 *
 * @package OnlineConvert\Exception
 */
class RequestException extends OnlineConvertSdkException
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
