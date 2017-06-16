<?php

namespace OnlineConvert\Helper;

/**
 * Class FileSystemHelper
 *
 * @codeCoverageIgnore
 *
 * @package OnlineConvert\Helper
 */
class FileSystemHelper
{
    /**
     * Encapsulates fopen
     *
     * @param string $source
     * @param string $mode
     *
     * @return bool|resource
     */
    public function fopen($source, $mode)
    {
        return fopen($source, $mode);
    }
}
