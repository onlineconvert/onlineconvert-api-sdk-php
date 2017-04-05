<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;

/**
 * Class to extend endpoints
 *
 * @see     http://apiv2.online-convert.com/#endpoints
 *
 * @package OnlineConvert\Endpoint
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Abstracted
{
    /**
     * Client to interact with the api
     *
     * @var Interfaced
     */
    protected $client;

    /**
     * Token from a job
     *
     * @var string
     */
    protected $userToken = null;

    /**
     * Abstracted constructor.
     *
     * @param Interfaced $client
     */
    public function __construct(Interfaced $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getUserToken()
    {
        return $this->userToken;
    }

    /**
     * @param string $userToken
     *
     * @return $this
     */
    public function setUserToken($userToken)
    {
        $this->userToken = $userToken;

        return $this;
    }

    /**
     * Deserialize json to array
     *
     * @param string $response as json
     *
     * @return array Associative from the json response
     */
    public function responseToArray($response)
    {
        return json_decode($response, true);
    }

    /**
     * @return Interfaced
     */
    public function getClient()
    {
        return $this->client;
    }
}
