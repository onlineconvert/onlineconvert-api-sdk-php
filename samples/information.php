<?php
/**
 * Gets information from API2
 */
require '../vendor/autoload.php';
use SwaggerClient\InformationApi;

$information_api = new InformationApi();

/**
 * Gets the available archive conversions from API
 *
 * GET http://api2.online-convert.com/conversions?category=archive&page=1 HTTP/1.1
 */
$conversions = $information_api->conversionsGet('archive', '', 1);
var_dump($conversions);

/**
 * Gets the available status codes from API
 *
 * GET http://api2.online-convert.com/status HTTP/1.1
 */
$status = $information_api->statusesGet();
var_dump($status);
