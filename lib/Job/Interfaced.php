<?php
/**
 * Created by PhpStorm.
 * User: andres
 * Date: 17/11/2015
 * Time: 22:46
 */

namespace Qaamgo\Job;

use Qaamgo\Models\Job as JobModel;
use Qaamgo\Models\OutputFile;
use Qaamgo\Models\Status;

interface Interfaced
{
    public function __construct($https = false, $host = null, $apiKey);

    /**
     * Return the status of the job
     *
     * @param $jobId
     * @return Status
     */
    public function getStatus($jobId);

    /**
     * Return the job information
     *
     * @param $jobId
     * @return JobModel
     */
    public function getJobInfo($jobId);


    /**
     * @param $jobId
     * @return OutputFile[]
     */
    public function getOutput($jobId);
}
