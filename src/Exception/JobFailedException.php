<?php
namespace OnlineConvert\Exception;

/**
 * Throwable when the job status is failed
 *
 * @see \OnlineConvert\Endpoint\JobsEndpoint::STATUS_*
 *
 * @package OnlineConvert\Exception
 *
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class JobFailedException extends OnlineConvertSdkException
{
}
