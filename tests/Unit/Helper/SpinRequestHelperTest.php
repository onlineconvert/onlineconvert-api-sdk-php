<?php

namespace Test\OnlineConvert\Unit\Helper;

use OnlineConvert\Helper\SpinRequestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\RequestInterface;

/**
 * Class SpinRequestHelperTest
 *
 * @package Test\OnlineConvert\Helper
 */
class SpinRequestHelperTest extends TestCase
{
    /**
     * The Object to be tested
     *
     * @var SpinRequestHelper
     */
    private $obj;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    public function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->obj = new SpinRequestHelper();
    }

    public function tearDown(): void
    {
        unset($this->obj);
    }

    /**
     * @dataProvider doSpinRequestDataProvider
     *
     * @param string $method
     * @param string $url
     * @param array  $options
     */
    public function testDoSpinRequestSuccess($method, $url, array $options, $retries)
    {
        $result = $this->createMock(ResponseInterface::class);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with($method, $url, $options)
            ->willReturn($result);

        $actual = $this->obj->doSpinRequest($method, $url, $options, $retries, $this->clientMock);
        $this->assertEquals($result, $actual);
    }

    /**
     * @dataProvider doSpinRequestExceptionDataProvider
     *
     * @param string $method
     * @param string $url
     * @param array  $options
     * @param int    $retries
     * @param int    $calls
     * @param int    $errorCode
     */
    public function testDoSpinRequestException($method, $url, array $options, $retries, $calls, $errorCode)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getInfo')
            ->willReturnCallback(function(string $key) use ($errorCode) {
                switch ($key) {
                    case 'http_code':
                        return $errorCode;
                    case 'url':
                        return 'someUrl';
                    case 'response_headers':
                        return ['HTTP/1.1 ' . $errorCode . ' exceptionMessage'];
                    default:
                        return null;
                }
            });
        $exception = new ServerException($response);

        $this->clientMock
            ->expects($this->exactly($calls))
            ->method('request')
            ->with($method, $url, $options)
            ->willThrowException($exception);

        $this->expectException(ServerException::class);
        $this->expectExceptionCode($errorCode);
        $this->expectExceptionMessage('exceptionMessage');

        $this->obj->doSpinRequest($method, $url, $options, $retries, $this->clientMock);
    }

    public function doSpinRequestDataProvider()
    {
        return [
            [
                'method'  => 'GET',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                0,
            ],
            [
                'method'  => 'POST',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                1,
            ],
            [
                'method'  => 'DELETE',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                100,
            ],
        ];
    }

    public function doSpinRequestExceptionDataProvider()
    {
        return [
            [
                'method'  => 'GET',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                0,
                6,
                429,
            ],
            [
                'method'  => 'POST',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                1,
                5,
                429,
            ],
            [
                'method'  => 'DELETE',
                'url'     => 'someUrl',
                'options' => ['someOption' => 'someValue'],
                100,
                1,
                429,
            ],
        ];
    }
}
