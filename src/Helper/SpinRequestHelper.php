<?php
namespace OnlineConvert\Helper;

use GuzzleHttp\Exception\RequestException;

/**
 * Class SpinRequestHelper
 */
class SpinRequestHelper
{
    /**
     * Maximum retries when the Request fails
     *
     * @const int
     */
    const MAX_RETRIES = 5;

    /**
     * Retry on these Status Codes
     *
     * @const array
     */
    const RETRY_ON_STATUS_CODES = [429, 503];

    /**
     * Does the Request and retries the request on failure with certain status codes until MAX_RETRIES is reached
     *
     * @param string             $method
     * @param string             $url
     * @param array              $options
     * @param int                $retries
     * @param \GuzzleHttp\Client $client
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function doSpinRequest($method, $url, array $options, $retries, $client)
    {
        $retries = (int)$retries;

        try {
            return $client->request(
                $method,
                $url,
                $options
            );
        } catch (RequestException $e) {
            if ($retries < self::MAX_RETRIES && in_array($e->getCode(), self::RETRY_ON_STATUS_CODES, true)) {
                $retries++;
                sleep($retries);
                $this->doSpinRequest($method, $url, $options, $retries, $client);
            } else {
                throw $e;
            }
        }
    }
}
