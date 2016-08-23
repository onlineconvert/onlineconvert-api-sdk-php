<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Exception\EndpointNotExistsException;

/**
 * Endpoints factory
 *
 * @package OnlineConvert\Endpoint
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class EndpointFactory
{
    /**
     * Client to interact with the api
     *
     * @var Interfaced
     */
    private $client;

    /**
     * EndpointFactory constructor.
     *
     * @param Interfaced $client
     */
    public function __construct(Interfaced $client)
    {
        $this->client = $client;
    }

    /**
     * Return a endpoint
     *
     * @throws EndpointNotExistsException when the endpoint class to create do not exist
     *
     * @param $endpointName
     *
     * @return Abstracted
     */
    public function getEndpoint($endpointName)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($endpointName) . 'Endpoint';

        if (!class_exists($class)) {
            throw new EndpointNotExistsException($class . ' not exists');
        }

        return new $class($this->client);
    }
}
