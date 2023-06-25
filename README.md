Online Convert API version 2 PHP SDK v 3
========================================

This SDK provides a code base to interact with the API version 2 of [Online-Convert.com](http://www.online-convert.com/)

[![Tests](https://github.com/onlineconvert/onlineconvert-api-sdk-php/actions/workflows/ci.yml/badge.svg)](https://github.com/onlineconvert/onlineconvert-api-sdk-php/actions/workflows/ci.yml)

Installation
------------
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
		'source' => '~/example.png'
	    ],
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
		'source' => 'http://cdn.online-convert.com/images/logo-top.png'
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
		'source' => '~/example.png'	            ],
	    [
		'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
		'source' => 'http://cdn.online-convert.com/images/logo-top.png'
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
	'callback' => 'http://alert.me/when/job/is/finished'
];

$syncJob = $syncApi->postFullJob($syncJob)->getJobCreated();
$asyncJob = $asyncApi->postFullJob($asyncJob)->getJobCreated();

var_dump($syncJob, $asyncJob);
```

### Sending a job with a gdrive_picker input

#### What is google drive picker
Google drive picker allows you to access files stored in your google drive account.

You can find information about it in [the official page](https://developers.google.com/picker/)

**Meaning of each field**:
* type
  * Specifies that we want to use the google drive picker input
* source
  * This must contain the **FILE ID** given back by the google drive picker
* credentials
  * This must be an array containing the credentials for the selected file
  * At the moment you only need to pass the **"token"** field inside it
    *  The **"token"** field is returned by the google drive picker when a file is selected
* content_type
  * This field is mandatory when you select a [Google Document](https://developers.google.com/drive/v3/web/mime-types)
  * You can leave this field empty if you are selecting any other kind of file
    * EG: pdf, png, zip...
* filename
  * This is the file name of the google drive picker file
  * If this field is not sent the file will be downloaded with the name `output.<extension>`

```
$job = [
    'input' => [
        [
            'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_GDRIVE_PICKER,
            'source' => '<put google drive picker file id here>',
            'credentials' => [
                'token' => '<put google drive picker token for the selected file here>'
            ],
            'content_type' => '<if this is a specific google document>',
            'filename' => '<name of the input file>'
        ]
    ],
    'conversion' => [
        [
            'target' => 'png'
        ]
    ]
];
```

After this just follow the previous examples on how to effectively send the job to the API.

#### Sending a job using Cloud storage providers

The following is an example to send a job where we want to convert a file that we have stored in our Amazon S3 storage.

If you want to know more please check our cloud storage **[API documentation](http://apiv2.online-convert.com/#cloud_storage)**

```
$job = [
    'input' => [
        [
            'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_CLOUD,
            'source' => 'amazons3',
            'parameters' => [
                'bucket' => 'your.bucket.name',
                'file' => 'the complete path to the file',
            ],
            'credentials' => [
                'accesskeyid' => 'your access key id',
                'secretaccesskey' => 'your secret access key',
            ]
        ]
    ],
    'conversion' => [
        [
            'target' => 'png'
        ]
    ]
];
```

After this just follow the previous examples on how to effectively send the job to the API.

#### Downloading the Converted Files

You can download the converted files using the following code snippet:

```
require_once __DIR__ . '/vendor/autoload.php';

$config = new \OnlineConvert\Configuration();
$config->setApiKey('main', 'PUT YOUR API KEY HERE');

// Remember to specify your own downloads folder.
$config->downloadFolder = __DIR__ . '/downloads';

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

>Also, you can create endpoint classes one by one if you like. For this, check **\OnlineConvert\Endpoint\Abstracted::_construct()**

>**IMPORTANT:** The class **\OnlineConvert\Endpoint\JobsEndpoint** can be used to send both synchronous and asynchronuos jobs; check **\OnlineConvert\Endpoint\JobsEndpoint::setAsync()**

>You can also set up different headers into the client, and do some process using the token of a job.


```
    $config = new \OnlineConvert\Configuration();
    $client = new \OnlineConvert\Client\OnlineConvertClient($config);
    $syncApi = new \OnlineConvert\Api($client);

    $ep = $syncApi->getJobsEndpoint();

    //Option 1
    $ep->getClient()->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_API_KEY, 'YOUR API KEY');

    //Option 2
    //$client->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_API_KEY, 'YOUR API KEY');
    //$ep->setClient($client);

    $job = $ep->postJob([]);

    $ip = $syncApi->getInputEndpoint();

    //WORK WITH TOKENS
    //OPTION 1
    $input = $ip->setUserToken($a['token'])->postJobInputRemote('http://www.online-convert.com/', $a['id']);

    //Option 2 (Apply also the previous options to set a header)
    //$ip->getClient()->setHeader(\OnlineConvert\Client\Interfaced::HEADER_OC_JOB_TOKEN, $a['token']);
    //$b = $ip->postJobInputRemote('http://www.online-convert.com/', $a['id']);

    var_dump($job, $input);
```

#### Manage exceptions

>If you catch **\OnlineConvert\Exception\OnlineConvertSdkException::class**, you automatically catch all exception in this SDK 

#### Getting url to upload file

- Send a job request
- Take the following keys from a job: **server** and **id**. You can use **\OnlineConvert\Api** or **\OnlineConvert\Endpoint\JobsEndpoint** to get the job information. You will get something like:

```
	[
	    [id] => 00000000-0000-0000-0000-000000000000
	    [token] => some_token_here
		    ...
	    [server] => http://www9.online-convert.com/dl
	    ...
	]
```
- Now with this information you can make this call:

```php
\OnlineConvert\Client\OnlineConvertClient->generateUrl(\OnlineConvert\Endpoint\Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $jobId])
```

- Also these token can be useful for some operations that you might want to share with your users

#### Uploading files directly to the [Online-Convert.com](http://www.online-convert.com/) servers via AJAX

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
