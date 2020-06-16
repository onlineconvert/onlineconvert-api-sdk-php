<?php

namespace Test\OnlineConvert\Unit\Handler;

use OnlineConvert\Exception\JobFailedException;
use OnlineConvert\Handler\CallbackHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class CallbackTest
 *
 * @package Test\OnlineConvert\Handler
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class CallbackTest extends TestCase
{
    /**
     * @var CallbackHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->handler = new CallbackHandler();
    }

    public function tearDown(): void
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
