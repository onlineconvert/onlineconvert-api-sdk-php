<?php

namespace Qaamgo\Helper;


/**
 * Class Common
 * @package Qaamgo\Helper
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Common
{
    public static function httpsToHttpVice($url, $https = false)
    {
        if ($https) {
            return preg_replace("/^http:/i", "https:", $url);
        }

        return preg_replace("/^https:/i", "http:", $url);
    }
}