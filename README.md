Online Convert API version 2 PHP SDK v 2
========================================

>This SDK provides a code base to interact with the API version 2 for [Online-Convert.com](http://www.online-convert.com/).

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

>The class **\OnlineConvert\Api** have some shortcuts methods from the endpoints *(EG: **\OnlineConvert\Api::postFullJob()**)* but you can get from this class the real endpoints and them methods.

>Also, you can create endpoints class one by one if you like. Check **\OnlineConvert\Endpoint\Abstracted::_construct()**

>**IMPORTANT** the class **\OnlineConvert\Endpoint\JobsEndpoint** can work to send syncronous and asyncronuos jobs, check **\OnlineConvert\Endpoint\JobsEndpoint::setAsync()**

#### Getting url to upload file

- Send a job request
- Get the nexts keys inside the job: **server** and **id**. You can use **\OnlineConvert\Api** or **\OnlineConvert\Endpoint\JobsEndpoint** to get the job information. You will get something like:

```
	[
	    [id] => 00000000-0000-0000-0000-000000000000
	    [token] => some_token_here
		    ...
	    [server] => http://www9.online-convert.com/dl
	    ...
	]
```
- Now with this information you can do:

```php
\OnlineConvert\Client\OnlineConvertClient->generateUrl(\OnlineConvert\Endpoint\Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $jobId])
```

- Also the token can be usefull for some operations thath you want to offer to your users

#### Uploading files directly to Online Convert Servers via AJAX

> **Real live case:** I want to send from my app the users files directly to the API because upload first to my app and then to the API is miss time.

>  **Considerations:** Normally this will save us time in process, space in the hard drive, and improve user experience, but here is good have a fallback without JS... just in case.

>The folling example is using **JQuery**.

>**IMPORTANT** The upload **MUST** be done one by one, so, you need one form per file.

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