<?php
/**
 * Created by PhpStorm.
 * User: andres
 * Date: 17/11/2015
 * Time: 22:46
 */

namespace Qaamgo\Job;


interface Interfaced
{
    public function __construct($https = false, $host = null, $apiKey);
    public function getStatus($jobId);
    public function getJobInfo($jobId);
}