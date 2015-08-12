# Online Convert API version 2 PHP SDK

This SDK provides a code base to interact with the API version 2 for [Online-Convert.com](http://www.online-convert.com/).

Since the API version 2 follows the [Swagger specs](http://swagger.io/), this code has been generated using the [swagger code generator](https://github.com/swagger-api/swagger-codegen), based on the current [API version 2 schema](https://api2.online-convert.com/schema).

## Installation

The Online-Convert.com PHP SDK can be installed using [Composer](https://getcomposer.org/). Add the Online-Convert.com PHP SDK package to your composer.json file.

    {
        "require": {
            "qaamgo/onlineconvert-api-sdk": "^1.0"
        }
    }

## Usage

Following, you will find an example for a simple GET status and GET conversions requests.

    require '../vendor/autoload.php';
    use SwaggerClient\InformationApi;
    
    $information_api = new InformationApi();
    $conversions = $information_api->conversionsGet('archive', '', 1);
    
    $information_api = new InformationApi();
    $status = $information_api->statusesGet();
    
    print_r($conversions);
    print_r($status);
    
## Samples

The SDK includes a few samples that are ready for work.