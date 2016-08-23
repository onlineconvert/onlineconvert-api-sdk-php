<?php
namespace OnlineConvert\Exception;

/**
 * Throwable when the callback is not defined for a job and \OnlineConvert\Endpoint\JobsEndpoint::async is true
 *
 * @see \OnlineConvert\Endpoint\JobsEndpoint::async
 *
 * @package OnlineConvert\Exception
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class CallbackNotDefinedException extends OnlineConvertSdkException
{
}
