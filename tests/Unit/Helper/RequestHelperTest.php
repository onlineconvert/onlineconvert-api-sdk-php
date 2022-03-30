<?php

namespace Test\OnlineConvert\Unit\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\HTTPMethodNotAllowed;
use OnlineConvert\Helper\FileSystemHelper;
use OnlineConvert\Helper\RequestHelper;
use OnlineConvert\Helper\SpinRequestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RequestHelperTest
 */
class RequestHelperTest extends TestCase
{
    /**
     * The Object to be tested
     *
     * @var RequestHelper
     */
    private $obj;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $spinRequestHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestExceptionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSystemHelper;

    public function setUp(): void
    {
        $this->clientMock            = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->spinRequestHelperMock = $this->getMockBuilder(SpinRequestHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemHelper      = $this->getMockBuilder(FileSystemHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock          = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestExceptionMock = $this->getMockBuilder(RequestException::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->obj                   = new RequestHelper($this->spinRequestHelperMock, $this->fileSystemHelper);
    }

    public function tearDown(): void
    {
        unset(
            $this->clientMock,
            $this->spinRequestHelperMock,
            $this->fileSystemHelper,
            $this->responseMock,
            $this->requestExceptionMock,
            $this->obj
        );
    }

    /**
     * @dataProvider sendRequestDataProvider
     *
     * @param string $url
     * @param string $method
     * @param array  $defaultHeader
     * @param array  $postData
     */
    public function testSendRequestSuccess($url, $method, array $defaultHeader, array $postData)
    {
        $response = new Response('200', [], 'foobar');
        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with($method, $url, ['body' => json_encode($postData), 'headers' => $defaultHeader], 0, $this->clientMock)
            ->willReturn($response);

        $result = $this->obj->sendRequest($url, $method, $defaultHeader, $this->clientMock, $postData);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @dataProvider sendRequestDataProvider
     *
     * @param string $url
     * @param string $method
     * @param array  $defaultHeader
     * @param array  $postData
     */
    public function testSendRequestException($url, $method, array $defaultHeader, array $postData)
    {
        $this->expectException(\OnlineConvert\Exception\RequestException::class);
        $this->setRequestExceptionMock();

        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with($method, $url, ['body' => json_encode($postData), 'headers' => $defaultHeader], 0, $this->clientMock)
            ->willThrowException($this->requestExceptionMock);

        $this->obj->sendRequest($url, $method, $defaultHeader, $this->clientMock, $postData);
    }

    /**
     * @dataProvider sendRequestDataProviderGet
     *
     * @param string $url
     * @param string $method
     * @param array  $defaultHeader
     * @param array  $postData
     */
    public function testSendRequestGetSuccess($url, $method, array $defaultHeader, array $postData)
    {
        $response = new Response('200', [], 'foobar');
        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with($method, $url, ['headers' => $defaultHeader], 0, $this->clientMock)
            ->willReturn($response);

        $result = $this->obj->sendRequest($url, $method, $defaultHeader, $this->clientMock, $postData);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @dataProvider sendRequestDataProviderGet
     *
     * @param string $url
     * @param string $method
     * @param array  $defaultHeader
     * @param array  $postData
     */
    public function testSendRequestIllegalResonseCode($url, $method, array $defaultHeader, array $postData)
    {
        $response = new Response(404, [], 'foobar');
        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with($method, $url, ['headers' => $defaultHeader], 0, $this->clientMock)
            ->willReturn($response);

        try {
            $result = $this->obj->sendRequest($url, $method, $defaultHeader, $this->clientMock, $postData);
        } catch (\OnlineConvert\Exception\RequestException $e) {
            $message = $e->getMessage();
        }
        $expected = 'Status code: 404, was not valid. Reason: foobar';
        $this->assertEquals($expected, $message);
    }

    /**
     * @dataProvider sendRequestDataProviderGet
     *
     * @param string $url
     * @param string $method
     * @param array  $defaultHeader
     * @param array  $postData
     */
    public function testSendRequestGetException($url, $method, array $defaultHeader, array $postData)
    {
        $this->expectException(\OnlineConvert\Exception\RequestException::class);

        $this->setRequestExceptionMock();

        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with($method, $url, ['headers' => $defaultHeader], 0, $this->clientMock)
            ->willThrowException($this->requestExceptionMock);

        $this->obj->sendRequest($url, $method, $defaultHeader, $this->clientMock, $postData);
    }

    public function sendRequestDataProvider()
    {
        return [
            [
                'url'           => 'some.url',
                'method'        => 'POST',
                'defaultHeader' => ['some-header' => 'some-value'],
                'postData'      => ['any' => 'somePostData'],
            ],
            [
                'url'           => 'some.url',
                'method'        => 'PATCH',
                'defaultHeader' => ['some-header' => 'some-value'],
                'postData'      => ['any' => 'somePostData'],
            ],
            [
                'url'           => 'some.url',
                'method'        => 'DELETE',
                'defaultHeader' => ['some-header' => 'some-value'],
                'postData'      => ['any' => 'somePostData'],
            ],
        ];
    }

    public function sendRequestDataProviderGet()
    {
        return [
            [
                'url'           => 'some.url',
                'method'        => 'foo',
                'defaultHeader' => ['some-header' => 'some-value'],
                'postData'      => ['any' => 'somePostData'],
            ],
            [
                'url'           => 'some.url',
                'method'        => '',
                'defaultHeader' => ['some-header' => 'some-value'],
                'postData'      => ['any' => 'somePostData'],
            ],
        ];
    }

    /**
     * @dataProvider postLocalFileProvider
     *
     * @param string      $source
     * @param string      $url
     * @param array       $defaultHeader
     * @param string|null $token
     */
    public function testPostLocalFileSuccess($source, $url, $defaultHeader, $token)
    {
        $response = new Response(200, [], 'foobarbaz');

        $this->fileSystemHelper
            ->expects($this->once())
            ->method('fopen')
            ->with($source, 'r')
            ->willReturn('foobar');

        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with(
                'POST',
                $url,
                [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => 'foobar',
                        ],
                    ],
                    'headers'   => $defaultHeader,
                ],
                0,
                $this->clientMock
            )
            ->willReturn($response);

        $this->obj->postLocalFile($source, $url, $defaultHeader, $this->clientMock, $token);
    }

    /**
     * @dataProvider postLocalFileProvider
     *
     * @param string      $source
     * @param string      $url
     * @param array       $defaultHeader
     * @param string|null $token
     */
    public function testPostLocalFileException($source, $url, $defaultHeader, $token)
    {
        $this->expectException(\OnlineConvert\Exception\RequestException::class);

        $this->setRequestExceptionMock();

        $this->fileSystemHelper
            ->expects($this->once())
            ->method('fopen')
            ->with($source, 'r')
            ->willReturn('foobar');

        $this->spinRequestHelperMock
            ->expects($this->once())
            ->method('doSpinRequest')
            ->with(
                'POST',
                $url,
                [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => 'foobar',
                        ],
                    ],
                    'headers'   => $defaultHeader,
                ],
                0,
                $this->clientMock
            )
            ->willThrowException($this->requestExceptionMock);

        $this->obj->postLocalFile($source, $url, $defaultHeader, $this->clientMock, $token);
    }

    public function postLocalFileProvider()
    {
        return [
            [
                'source'        => 'someSource',
                'url'           => 'some.url',
                'defaultHeader' => ['some-header' => 'some-value'],
                'token'         => null,
            ],
            [
                'source'        => 'someSource',
                'url'           => 'some.url',
                'defaultHeader' => ['some-header' => 'some-value', 'X-OC-TOKEN' => 'someToken'],
                'token'         => 'someToken',
            ],
        ];
    }

    /**
     * @dataProvider checkMethodToSendRequestSuccessDataProvider
     *
     * @param string $method
     */
    public function testCheckMethodToSendRequestSuccess($method)
    {
        try {
            $this->obj->checkMethodToSendRequest($method);
        } catch (HTTPMethodNotAllowed $exception) {
            $message = $exception->getMessage();
        }
        $this->assertTrue(!isset($message));
    }

    public function checkMethodToSendRequestSuccessDataProvider()
    {
        return [
          [
              'method' => OnlineConvertClient::METHOD_DELETE
          ],
          [
              'method' => OnlineConvertClient::METHOD_GET
          ],
          [
              'method' => OnlineConvertClient::METHOD_PATCH
          ],
          [
              'method' => OnlineConvertClient::METHOD_POST
          ],
        ];
    }

    /**
     * @dataProvider checkMethodToSendRequestExceptionDataProvider
     *
     * @param string $method
     */
    public function testCheckMethodToSendRequestException($method)
    {
        try {
            $this->obj->checkMethodToSendRequest($method);
        } catch (HTTPMethodNotAllowed $exception) {
            $message = $exception->getMessage();
        }
        $this->assertEquals($method . ' is not allowed', $message);
    }

    public function checkMethodToSendRequestExceptionDataProvider()
    {
        return [
            [
                'method' => 'HEAD'
            ],
            [
                'method' => 'options'
            ],
            [
                'method' => ''
            ],
        ];
    }

    /**
     * Sets common methods for the RequestException Mock
     */
    private function setRequestExceptionMock()
    {
        $this->requestExceptionMock
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
    }
}
