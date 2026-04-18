<?php

namespace Test\OnlineConvert\Unit\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OnlineConvert\Helper\SpinRequestHelper;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class SpinRequestHelperTest
 *
 * @package Test\OnlineConvert\Helper
 */
#[AllowMockObjectsWithoutExpectations]
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
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->obj = new SpinRequestHelper();
    }

    public function tearDown(): void
    {
        unset($this->obj);
    }

    #[DataProvider('doSpinRequestDataProvider')]
    public function testDoSpinRequestSuccess($method, $url, array $options, $retries)
    {
        $result = new Response(200, [], 'foo');
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with($method, $url, $options)
            ->willReturn($result);

        $actual = $this->obj->doSpinRequest($method, $url, $options, $retries, $this->clientMock);
        $this->assertEquals($result, $actual);
    }

    #[DataProvider('doSpinRequestExceptionDataProvider')]
    public function testDoSpinRequestException($method, $url, array $options, $retries, $calls, $errorCode)
    {
        $request   = new Request('GET', 'any.url');
        $response  = new Response($errorCode);
        $exception = new RequestException('exceptionMessage', $request, $response);

        $this->clientMock
            ->expects($this->exactly($calls))
            ->method('request')
            ->with($method, $url, $options)
            ->willThrowException($exception);

        $this->expectException(RequestException::class);
        $this->expectExceptionCode($errorCode);
        $this->expectExceptionMessage('exceptionMessage');

        $this->obj->doSpinRequest($method, $url, $options, $retries, $this->clientMock);
    }

    public static function doSpinRequestDataProvider()
    {
        return [
            [
                'GET',
                'someUrl',
                ['someOption' => 'someValue'],
                0,
            ],
            [
                'POST',
                'someUrl',
                ['someOption' => 'someValue'],
                1,
            ],
            [
                'DELETE',
                'someUrl',
                ['someOption' => 'someValue'],
                100,
            ],
        ];
    }

    public static function doSpinRequestExceptionDataProvider()
    {
        return [
            [
                'GET',
                'someUrl',
                ['someOption' => 'someValue'],
                0,
                6,
                429,
            ],
            [
                'POST',
                'someUrl',
                ['someOption' => 'someValue'],
                1,
                5,
                429,
            ],
            [
                'DELETE',
                'someUrl',
                ['someOption' => 'someValue'],
                100,
                1,
                429,
            ],
        ];
    }
}
