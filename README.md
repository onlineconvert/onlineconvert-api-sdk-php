Online Convert API version 2 PHP SDK v 2
========================================

>This SDK provides a code base to interact with the API version 2 of [Online-Convert.com](http://www.online-convert.com/).

Installation
------------
####Composer

```json
{
	"require": {
	    "qaamgo/onlineconvert-api-sdk": "^2.0"
	}
}
```

Getting started
---------------

####Configuration

```php
require 'vendor/autoload.php';

$config = new \OnlineConvert\Configuration();
$config->setApiKey('main', 'HERE YOUR API KEY');
$client = new \OnlineConvert\Client\OnlineConvertClient($config, 'main');
$syncApi = new \OnlineConvert\Api($client);
$asyncApi = new \OnlineConvert\Api($client, true);
```

####Sending a full job

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

Advanced usage
--------------

>The class **\OnlineConvert\Api::class** has some shortcut methods that links it to  the endpoints classes *(EG: **\OnlineConvert\Api::postFullJob()**)*, but from the class you can call the real endpoint and their methods.

>Also, you can create endpoint classes one by one if you like. For this, check **\OnlineConvert\Endpoint\Abstracted::_construct()**

>**IMPORTANT:** The class **\OnlineConvert\Endpoint\JobsEndpoint** can be used to send both synchronous and asynchronuos jobs; check **\OnlineConvert\Endpoint\JobsEndpoint::setAsync()**

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