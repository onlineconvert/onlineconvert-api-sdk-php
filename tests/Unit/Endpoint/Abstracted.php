<?php

namespace Test\OnlineConvert\Unit\Endpoint;

use OnlineConvert\Client\Interfaced;
use PHPUnit\Framework\TestCase;

class Abstracted extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * A Job id in UUID format
     *
     * @var string
     */
    protected $aJobId = '00000000-1111-2222-3333-444444444444';

    /**
     * A Conversion id in UUID format
     *
     * @var string
     */
    protected $aConversionId = 'cccccccc-cccc-cccc-cccc-cccccccccccc';

    /**
     * A token
     *
     * @var string
     */
    protected $aToken = '01234567890123456789012345678901';

    /**
     * A server URL
     *
     * @var string
     */
    protected $aServer = 'http://www4.online-convert.com';

    /**
     * Method called before starting each test
     */
    public function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Interfaced::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Method called at the end of each test
     */
    public function tearDown(): void
    {
        unset($this->clientMock);
    }

    /**
     * To be used while checking for invalid arguments to be sure that no HTTP API requests are sent
     */
    protected function setClientMockToNeverCallMethods()
    {
        $this->clientMock
            ->expects($this->never())
            ->method('generateUrl');
        $this->clientMock
            ->expects($this->never())
            ->method('sendRequest');
    }
}
