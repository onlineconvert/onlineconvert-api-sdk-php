<?php
/**
 * Creates a new job from API2.
 *
 * POST http://api2.online-convert.com/jobs?key=KEY HTTP/1.1
 * Content-Type: application/json
 *
 * {
 *    "input": [{
 *      "source": "http://bit.ly/b2dlVA"
 *    }],
 *    "conversion": [{
 *      "target": "png"
 *    }],
 * }
 */
define('API_KEY', 'DEFINE_API_KEY_HERE');
require '../vendor/autoload.php';

$job = new \SwaggerClient\models\Job();

$conversion = new \SwaggerClient\models\Conversion();
$conversion->target = 'png';
$job->conversion = array($conversion);

$inputFile = new \SwaggerClient\models\InputFile();
$inputFile->source = 'http://bit.ly/b2dlVA';
$job->input = array($inputFile);

$job_api = new \SwaggerClient\JobsApi();
$created_job = $job_api->jobsPost(API_KEY, $job);

var_dump($created_job);
