<?php
namespace Test\OnlineConvert\Handler;

use OnlineConvert\Exception\JobFailedException;
use OnlineConvert\Handler\CallbackHandler;


/**
 * Class CallbackTest
 *
 * @package Test\OnlineConvert\Handler
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallbackHandler
     */
    private $handler;

    public function setUp()
    {
        $this->handler = new CallbackHandler();
    }

    public function tearDown()
    {
        unset(
            $this->handler
        );
    }

    public function testJobHasExpectedStatusOnCompleted()
    {
        $job['id'] = '00000000';
        $job['status'] = [
            'code' => 'completed',
            'info' => 'The file has been successfully converted.'
        ];

        $this->assertTrue($this->handler->jobHasExpectedStatus($job));
    }

    public function testJobHasExpectedStatusOnFailed()
    {
        $this->expectException(JobFailedException::class);

        $job['id'] = '00000000';
        $job['status'] = [
            'code' => 'failed',
            'info' => 'The file has not been convert due errors.'
        ];


        $this->handler->jobHasExpectedStatus($job);
    }
}
