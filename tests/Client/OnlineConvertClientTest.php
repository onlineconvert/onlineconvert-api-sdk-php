<?php
namespace Test\OnlineConvert\Client;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Configuration;
use OnlineConvert\Endpoint\Resources;


/**
 * Class OnlineConvertClientTest
 *
 * @package Test\OnlineConvert\Client
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class OnlineConvertClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OnlineConvertClient
     */
    private $client;

    /**
     * @var Configuration
     */
    private $config;

    public function setUp()
    {
        $this->config = new Configuration();
        $this->config->setApiKey('main', 'some_key');

        $this->client = new OnlineConvertClient($this->config, 'main');
    }

    public function tearDown()
    {
        unset(
            $this->mockClient,
            $this->client
        );
    }

    public function testGenerateUrl()
    {
        $server = 'http://some.foo';
        $id     = '0000000000';

        $expect = $server . '/upload-file/' . $id;

        $actual = $this->client->generateUrl(Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $id]);

        $this->assertEquals($expect, $actual);
    }
}
