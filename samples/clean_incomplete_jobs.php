<?php
/**
 * Creates a new job from API2.
 */
define('API_KEY', 'DEFINE_API_KEY_HERE');

require '../vendor/autoload.php';

$job_api = new \SwaggerClient\JobsApi();
$jobs = $job_api->jobsGet('incomplete', null, API_KEY, 1);
if ($jobs) {
    foreach ($jobs as $job) {
        try {
            $job_api->jobsJobIdDelete(null, API_KEY, $job->id);
            echo 'Removed job' . $job->id . "\n";

        } catch (\SwaggerClient\ApiException $exception) {
            echo $exception->getMessage();
        }
    }
}