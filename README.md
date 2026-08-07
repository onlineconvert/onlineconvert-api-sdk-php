> [!WARNING]
> ## ⚠️ This SDK is deprecated
> **`qaamgo/onlineconvert-api-sdk` is no longer maintained.** Please migrate to the new
> **API2Convert PHP SDK** → **https://github.com/QaamGo/api2convert-php**
> (Packagist: [`api2convert/sdk`](https://packagist.org/packages/api2convert/sdk)).

## Migrating to the new SDK

**Install the new package:**

```bash
composer require api2convert/sdk
```

**Convert a file** — what used to be a multi-step job flow (create job → add input → set
conversion → start → poll → download) is now a single call:

```php
$client = new Api2Convert\Api2Convert('YOUR_API_KEY');
$client->convert('photo.png', 'jpg')->save('photo.jpg');
```

See the new SDK's README for the full API: <https://github.com/QaamGo/api2convert-php>

---

Online Convert API version 2 PHP SDK
====================================

This SDK provides a code base to interact with the API version 2 of [api2convert.com](https://www.api2convert.com/)

Requirements
------------
- PHP 8.3 or later 
- `guzzlehttp/guzzle`

[![Tests](https://github.com/onlineconvert/onlineconvert-api-sdk-php/actions/workflows/ci.yml/badge.svg)](https://github.com/onlineconvert/onlineconvert-api-sdk-php/actions/workflows/ci.yml)

Installation
------------

> [!WARNING]
> This package is abandoned. New integrations should use
> [`api2convert/sdk`](https://packagist.org/packages/api2convert/sdk) instead — see
> [Migrating to the new SDK](#migrating-to-the-new-sdk) above.

The recommended way to install is through [Composer](https://getcomposer.org/).
```bash
composer require qaamgo/onlineconvert-api-sdk
```

Getting started
---------------

#### Configuration

```php
require 'vendor/autoload.php';

$config = new \OnlineConvert\Configuration();
$config->setApiKey('main', 'HERE YOUR API KEY');
$client = new \OnlineConvert\Client\OnlineConvertClient($config, 'main');
$syncApi = new \OnlineConvert\Api($client);
$asyncApi = new \OnlineConvert\Api($client, true);
```

#### Sending a full job

```php
$syncJob = [
	'input' => [
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_UPLOAD,
		'source' => '/path/to/example.png'
	    ],
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
		'source' => 'https://example-files.online-convert.com/raster%20image/png/example_small.png'
	    ]
	],
	'conversion' => [
	    [
		'target' => 'png'
	    ],
	    [
		'target' => 'mp4'
	    ]
	]
];

$asyncJob = [
	'input' => [
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_UPLOAD,
		'source' => '/path/to/example.png'
	    ],
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
		'source' => 'https://example-files.online-convert.com/raster%20image/png/example_small.png'
	    ]
	],
	'conversion' => [
	    [
		'target' => 'png'
	    ],
	    [
		'target' => 'mp4'
	    ]
	],
	'callback' => 'https://example.com/when/job/is/finished'
];

$syncJob = $syncApi->postFullJob($syncJob)->getJobCreated();
$asyncJob = $asyncApi->postFullJob($asyncJob)->getJobCreated();

var_dump($syncJob, $asyncJob);
```



#### Downloading the Converted Files

You can download the converted files using the following code snippet:

```php
require_once __DIR__ . '/vendor/autoload.php';

$config = new \OnlineConvert\Configuration();
$config->setApiKey('main', 'PUT YOUR API KEY HERE');

// Remember to specify your own downloads folder. The SDK does not create it for you —
// downloading into a folder that does not exist fails with a Guzzle RuntimeException.
$downloadFolder = __DIR__ . '/downloads';

if (!is_dir($downloadFolder) && !mkdir($downloadFolder, 0775, true) && !is_dir($downloadFolder)) {
    throw new \RuntimeException('Could not create the download folder: ' . $downloadFolder);
}

$config->downloadFolder = $downloadFolder;

$client = new \OnlineConvert\Client\OnlineConvertClient($config, 'main');
$syncApi = new \OnlineConvert\Api($client);
$outputEndpoint = $syncApi->getOutputEndpoint();

$syncJob = [
    'input' => [
        [
            'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
            'source' => 'PUT URL HERE'
        ]
    ],
    'conversion' => [
        [
            'target' => 'jpg'
        ]
    ]
];

$job = $syncApi->postFullJob($syncJob)->getJobCreated();

// You will find the file/s in the downloads folder
$outputEndpoint->downloadOutputs($job);
```

Advanced usage
--------------

>The class **\OnlineConvert\Api::class** has some shortcut methods that links it to  the endpoints classes *(EG: **\OnlineConvert\Api::postFullJob()**)*, but from the class you can call the real endpoint and their methods.

>Also, you can create endpoint classes one by one if you like. For this, check **\OnlineConvert\Endpoint\Abstracted::__construct()**

>**IMPORTANT:** The class **\OnlineConvert\Endpoint\JobsEndpoint** can be used to send both synchronous and asynchronuos jobs; check **\OnlineConvert\Endpoint\JobsEndpoint::setAsync()**

>You can also set up different headers into the client, and do some process using the token of a job.


```php
    $config = new \OnlineConvert\Configuration();
    $client = new \OnlineConvert\Client\OnlineConvertClient($config);
    $syncApi = new \OnlineConvert\Api($client);

    $ep = $syncApi->getJobsEndpoint();

    //Option 1
    $ep->getClient()->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_API_KEY, 'YOUR API KEY');

    //Option 2 (every endpoint shares the same client instance, so this affects $ep too)
    //$client->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_API_KEY, 'YOUR API KEY');

    $job = $ep->postJob([]);

    $ip = $syncApi->getInputEndpoint();

    //WORK WITH TOKENS
    //OPTION 1
    $input = $ip->setUserToken($job['token'])->postJobInputRemote('https://www.online-convert.com/', $job['id']);

    //Option 2 (Apply also the previous options to set a header)
    //$ip->getClient()->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_JOB_TOKEN, $job['token']);
    //$input = $ip->postJobInputRemote('https://www.online-convert.com/', $job['id']);

    var_dump($job, $input);
```

#### Manage exceptions

>If you catch **\OnlineConvert\Exception\OnlineConvertSdkException::class**, you automatically catch all exception in this SDK 

#### Getting url to upload file

- Send a job request
- Take the following keys from a job: **server** and **id**. You can use **\OnlineConvert\Api** or **\OnlineConvert\Endpoint\JobsEndpoint** to get the job information. You will get something like:

```text
	[
	    [id] => 00000000-0000-0000-0000-000000000000
	    [token] => some_token_here
		    ...
	    [server] => https://www2.api2convert.com/v2/dl/web7
	    ...
	]
```
- Now with this information you can make this call:

```php
$uploadUrl = $client->generateUrl(\OnlineConvert\Endpoint\Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $jobId]);
```

- Also these token can be useful for some operations that you might want to share with your users

#### Uploading files directly to the [Online-Convert.com](https://www.online-convert.com/) servers via AJAX

> **Real life case:** I want to send our user files directly to the API, because sending them to my app first and then to the API costs too much time.

>  **Considerations:** Normally, this will save us process time, space on the hard drive and improve user experience, but it's advised to have a fallback alternative that works without using JS.

>The following example is using **JQuery**.

>**IMPORTANT:** The upload **MUST** be done one by one. Thus, you need one form per file.

#### HTML CODE
```html
<form id="postFile" enctype="multipart/form-data" method="post" action="javascript:;" accept-charset="utf-8">
        <input name="file" type="file" id="file"/> <input type="submit" id="upload-btn"/>
</form>
```
#### JS
```javascript
$(document).ready(function () {
        $('#postFile').submit(function (event) {
            event.preventDefault();

            var formData = new FormData($(this)[0]);

            $.ajax({
                url: 'URL_GENERATED_TO_UPLOAD_FILE',
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                processData: false,
                contentType: false,
                mimeType: 'multipart/form-data',
                headers: {
                    'x-oc-token': 'JOB_TOKEN_HERE',
                    'cache-control': 'no-cache'
                },
                success: function () {
                    window.alert('Success');
                },
                error: function () {
                    window.alert('Error');
                }
            });
        });
    });
```

License
-------

Apache-2.0 — see [LICENSE](LICENSE).
