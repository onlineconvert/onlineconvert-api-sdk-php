<?php

namespace OnlineConvert\Exception;

/**
 * Throwable when the requested status cannot be used in the actual context.
 * E.g. if you try to patch a completed job
 *
 * @package OnlineConvert\Exception
 */
class InvalidStatusException extends OnlineConvertSdkException
{
}
