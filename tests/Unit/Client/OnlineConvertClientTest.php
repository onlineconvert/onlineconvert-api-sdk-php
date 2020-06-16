<?php

namespace Test\OnlineConvert\Unit\Client;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Configuration;
use OnlineConvert\Endpoint\Resources;
use PHPUnit\Framework\TestCase;


/**
 * Class OnlineConvertClientTest
 *
 * @package Test\OnlineConvert\Client
 */
class OnlineConvertClientTest extends TestCase
{
    /**
     * @var OnlineConvertClient
     */
    private $client;

    /**
     * @var Configuration
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new Configuration();
        $this->config->setApiKey('main', 'some_key');
    }

    public function tearDown(): void
    {
        unset(
            $this->mockClient,
            $this->client
        );
    }

    public function testGenerateUrl()
    {
        $this->client = new OnlineConvertClient($this->config, 'main');

        $server = 'http://some.foo';
        $id     = '0000000000';

        $expect = $server . '/upload-file/' . $id;
        $actual = $this->client->generateUrl(Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $id]);

        $this->assertEquals($expect, $actual);
    }

    public function testGenerateUrlHttps()
    {
        $this->config->https = true;

        $this->client = new OnlineConvertClient($this->config, 'main');

        $server = 'http://some.foo';
        $id     = '0000000000';

        $expect = $server . '/upload-file/' . $id;
        $actual = $this->client->generateUrl(Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $id]);

        $this->assertEquals($expect, $actual);
    }

    public function testGenerateUrlWithQuery()
    {
        $this->client = new OnlineConvertClient($this->config, 'main');

        $server = 'http://some.foo';
        $id     = '0000000000';

        $expect = $server . '/upload-file/' . $id . '?a=1&b=2';
        $actual = $this->client->generateUrl(
            Resources::URL_POST_FILE,
            ['server' => $server, 'job_id' => $id],
            ['a' => '1', 'b' => '2']
        );

        $this->assertEquals($expect, $actual);
    }

    public function testGenerateUrlWithoutParam()
    {
        $this->client = new OnlineConvertClient($this->config, 'main');

        $server = 'http://some.foo';
        $id     = '0000000000';

        $expect = 'foo';
        $actual = $this->client->generateUrl('foo', ['server' => $server, 'job_id' => $id]);

        $this->assertEquals($expect, $actual);
    }

    /**
     * @dataProvider getHeaderDataProvider
     *
     * @param string $headerKey
     * @param string|boolea $expected
     */
    public function testGetHeader($headerKey, $expected)
    {
        $this->client = new OnlineConvertClient($this->config, 'main');

        $result = $this->client->getHeader($headerKey);
        $this->assertEquals($expected, $result);
    }

    public function getHeaderDataProvider()
    {
        return [
            [
                'headerKey' => 'foo',
                'expected' => false
            ],
            [
                'headerKey' => 'User-Agent',
                'expected' => OnlineConvertClient::CLIENT_USER_AGENT
            ],
            [
                'headerKey' => OnlineConvertClient::HEADER_OC_SDK_CLIENT_VERSION,
                'expected' => 2
            ],
        ];
    }
}
