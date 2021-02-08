<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Exception\FileNotExists;
use OnlineConvert\Exception\InvalidEngineException;
use OnlineConvert\Exception\OnlineConvertSdkException;
use OnlineConvert\Exception\RequestException;

/**
 * Manage Input Endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class InputEndpoint extends Abstracted
{
    /**
     * @var string
     */
    const INPUT_TYPE_UPLOAD = 'upload';

    /**
     * @var string
     */
    const INPUT_TYPE_REMOTE = 'remote';

    /**
     * @var string
     */
    const INPUT_TYPE_INPUT_ID = 'input_id';

    /**
     * @var string
     */
    const INPUT_TYPE_GDRIVE_PICKER = 'gdrive_picker';

    /**
     * @var string
     */
    const INPUT_TYPE_CLOUD = 'cloud';

    /**
     * @var string
     */
    const ENGINE_AUTO = 'auto';

    /**
     * @var string
     */
    const ENGINE_VIDEO = 'video';

    /**
     * @var string
     */
    const ENGINE_FILE = 'file';

    /**
     * @var string
     */
    const ENGINE_WEBSITE = 'website';

    /**
     * @var string
     */
    const ENGINE_SCREENSHOT = 'screenshot';

    /**
     * @var string
     */
    const ENGINE_SCREENSHOT_PDF = 'screenshot_pdf';

    /**
     * @var array
     */
    const ENGINES = [
        self::ENGINE_AUTO,
        self::ENGINE_VIDEO,
        self::ENGINE_FILE,
        self::ENGINE_WEBSITE,
        self::ENGINE_SCREENSHOT,
        self::ENGINE_SCREENSHOT_PDF,
    ];

    /**
     * Shortcut to post inputs with different type.
     * The 'type' key inside the input array must be equal to one of the constants provided in this class.
     *
     * @api
     *
     * @param array $input If the type is self::INPUT_TYPE_INPUT_ID, the source is in UUID format
     *                     If the type is self::INPUT_TYPE_GDRIVE_PICKER, the input is in the format
     *                     [
     *                         'type'        => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_GDRIVE_PICKER,
     *                         'source'      => 'insert-gdrive-file-id-here',
     *                         'filename     => 'file_name',
     *                         'content_type => 'content/type,
     *                         'credentials  => ['token' => 'authorization_token']
     *                     ]
     *                     if the type is self::INPUT_TYPE_UPLOAD or self::INPUT_TYPE_REMOTE
     *                     [
     *                         'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_UPLOAD,
     *                         'source' => '/your/source',
     *                     ]
     * @param array $job
     *
     * @return array
     *
     * @throws RequestException          If the passed arrays missed mandatory fields
     * @throws OnlineConvertSdkException when error on the request
     */
    public function postJobInput(array $input, array $job)
    {
        $errors = [];

        if (empty($job['id'])) {
            $errors[] = 'Job id is mandatory';
        }

        if (empty($input['type'])) {
            $errors[] = 'Input type is mandatory';
        }

        if (empty($input['source'])) {
            $errors[] = 'Input source is mandatory';
        }

        $errors = array_merge($errors, $this->checkParameters($input, $job));

        if ($errors) {
            $exceptionMessage = implode(PHP_EOL, $errors);
            throw new RequestException($exceptionMessage);
        }

        switch ($input['type']) {
            case self::INPUT_TYPE_UPLOAD:
                return $this->postJobInputUpload(
                    $input['source'],
                    $job['id'],
                    $job['server'],
                    $job['token']
                );
                break;
            case self::INPUT_TYPE_INPUT_ID:
                return $this->postJobInputInputId(
                    $input['source'],
                    $job['id']
                );
                break;
            case self::INPUT_TYPE_GDRIVE_PICKER:
                $input['filename']     = empty($input['filename']) ? '' : $input['filename'];
                $input['content_type'] = empty($input['content_type']) ? '' : $input['content_type'];

                return $this->postJobInputGdrivePicker(
                    $job['id'],
                    $input['source'],
                    $input['credentials'],
                    $input['filename'],
                    $input['content_type']
                );
                break;
            case self::INPUT_TYPE_CLOUD:
                $input['parameters']  = empty($input['parameters']) ? [] : $input['parameters'];
                $input['credentials'] = empty($input['credentials']) ? [] : $input['credentials'];

                $this->postJobInputCloud(
                    $job['id'],
                    $input['source'],
                    $input['parameters'],
                    $input['credentials']
                );
                break;
            default:
                $engine = self::ENGINE_AUTO;

                if (!empty($input['engine'])) {
                    $engine = $input['engine'];
                }

                if (!in_array($engine, self::ENGINES, true)) {
                    throw new InvalidEngineException('Engine ' . $engine . ' does not exist');
                }

                return $this->postJobInputRemote($input['source'], $job['id'], $engine);
        }
    }

    /**
     * Get the inputs of the job with the given job id.
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobInputs($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Post remote input
     *
     * @param string $source
     * @param string $jobId
     * @param string $engine
     *
     * @return array
     */
    public function postJobInputRemote($source, $jobId, $engine = self::ENGINE_AUTO)
    {
        if (!in_array($engine, self::ENGINES, true)) {
            throw new InvalidEngineException('Engine ' . $engine . ' does not exist');
        }

        $input = [
            'type'   => self::INPUT_TYPE_REMOTE,
            'source' => $source,
            'engine' => $engine,
        ];

        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_POST,
                $input,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Post input_id from previous conversion as input
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $source
     * @param string $jobId
     *
     * @return array
     */
    public function postJobInputInputId($source, $jobId)
    {
        $input = [
            'type'   => self::INPUT_TYPE_INPUT_ID,
            'source' => $source,
        ];

        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_POST,
                $input,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Upload input
     *
     * @api
     *
     * @throws FileNotExists             when the source does not exists
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string      $source
     * @param string      $jobId
     * @param string      $server
     * @param string|null $token
     *
     * @return array
     */
    public function postJobInputUpload($source, $jobId, $server, $token = null)
    {
        $url = $this->client
            ->generateUrl(Resources::URL_POST_FILE, ['server' => $server, 'job_id' => $jobId]);

        $source = realpath($source);

        if (!$source) {
            throw new FileNotExists($source . ' do not exists');
        }

        return $this->responseToArray($this->client->postLocalFile($source, $url, $token));
    }

    /**
     * Post Google Drive picker input
     *
     * @api
     *
     * @throws OnlineConvertSdkException when there is error on the request
     *
     * @param string $jobId
     * @param string $source
     * @param array  $credentials
     * @param string $filename
     * @param string $contentType
     *
     * @return array
     */
    public function postJobInputGdrivePicker($jobId, $source, array $credentials, $filename = '', $contentType = '')
    {
        $input = [
            'type'         => self::INPUT_TYPE_GDRIVE_PICKER,
            'source'       => $source,
            'filename'     => $filename,
            'content_type' => $contentType,
            'credentials'  => $credentials,
        ];

        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_POST,
                $input,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Post cloud input
     *
     * @api
     *
     * @throws OnlineConvertSdkException when there is error on the request
     *
     * @param string $jobId
     * @param string $source
     * @param array  $parameters
     * @param array  $credentials
     *
     * @return array
     */
    public function postJobInputCloud($jobId, $source, array $parameters, array $credentials)
    {
        $input = [
            'type'        => self::INPUT_TYPE_CLOUD,
            'source'      => $source,
            'parameters'  => $parameters,
            'credentials' => $credentials,
        ];

        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $jobId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_POST,
                $input,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Get a specific input of a job by the ids given
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     * @param string $inputId
     *
     * @return array
     */
    public function getJobInput($jobId, $inputId)
    {
        $url = $this->client
            ->generateUrl(Resources::JOB_ID_INPUT_ID, ['job_id' => $jobId, 'input_id' => $inputId]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_GET,
                null,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }

    /**
     * Delete a specific input of a job by the given ids
     *
     * @api
     *
     * @throws OnlineConvertSdkException when the deletion was not successfully - request error
     *
     * @param string $jobId
     * @param string $inputId
     *
     * @return bool TRUE when the deletion was successfully
     */
    public function deleteJobInput($jobId, $inputId)
    {
        $url = $this->client
            ->generateUrl(Resources::JOB_ID_INPUT_ID, ['job_id' => $jobId, 'input_id' => $inputId]);

        $this->client->sendRequest(
            $url,
            Interfaced::METHOD_DELETE,
            [],
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return true;
    }

    /**
     * Check mandatory parameters. Returns an array with all the missed parameters.
     *
     * @param array $input
     * @param array $job
     *
     * @return array
     */
    private function checkParameters(array $input, array $job)
    {
        $errors = [];

        switch ($input['type']) {
            case self::INPUT_TYPE_UPLOAD:
                if (empty($job['server'])) {
                    $errors[] = 'Job server is mandatory';
                }

                if (empty($job['token'])) {
                    $errors[] = 'Job token is mandatory';
                }
                break;
            case self::INPUT_TYPE_GDRIVE_PICKER:
                if (empty($input['credentials']['token'])) {
                    $errors[] = 'Credentials with token are mandatory';
                }
                break;
        }

        return $errors;
    }
    /**
     * Patch a input
     *
     * @api
     *
     * @param string $jobId
     * @param string $inputId
     * @param array $input
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @return array
     */
    public function patchInput($jobId, $inputId, array $input)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_INPUT_ID, ['job_id' => $jobId, 'input_id' => $inputId]);

        $response = $this->client->sendRequest(
            $url,
            Interfaced::METHOD_PATCH,
            $input,
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return $this->responseToArray($response);
    }

    /**
     * Shortcut to post multiple inputs with different type.
     * The 'type' key inside the input array must be equal to one of the constants provided in this class.
     *
     * @api
     *
     * @param array $inputs If the type is self::INPUT_TYPE_INPUT_ID, the source is in UUID format
     *                     If the type is self::INPUT_TYPE_GDRIVE_PICKER, the input is in the format
     *                     [
     *                         'type'        => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_GDRIVE_PICKER,
     *                         'source'      => 'insert-gdrive-file-id-here',
     *                         'filename     => 'file_name',
     *                         'content_type => 'content/type,
     *                         'credentials  => ['token' => 'authorization_token']
     *                     ],
     *                     [
     *                         'type'        => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_GDRIVE_PICKER,
     *                         'source'      => 'insert-gdrive-file-id-here',
     *                         'filename     => 'file_name',
     *                         'content_type => 'content/type,
     *                         'credentials  => ['token' => 'authorization_token']
     *                     ]
     *                     if the type is  self::INPUT_TYPE_REMOTE
     *                     [
     *                         'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
     *                         'source' => '/your/source',
     *                     ],
     *                     [
     *                         'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_REMOTE,
     *                         'source' => '/your/source',
     *                     ]
     * @param array $job
     *
     * @return array
     *
     * @throws RequestException          If the passed arrays missed mandatory fields
     * @throws OnlineConvertSdkException when error on the request
     */
    public function postJobInputs($inputs, $job)
    {
        $errors = [];

        if (empty($job['id'])) {
            $errors[] = 'Job id is mandatory';
        }

        if (empty($inputs)) {
            $errors[] = 'Input source is mandatory';
        }

        if ($errors) {
            $exceptionMessage = implode(PHP_EOL, $errors);
            throw new RequestException($exceptionMessage);
        }

        $url = $this->client->generateUrl(Resources::JOB_ID_INPUTS, ['job_id' => $job['id']]);

        return $this->responseToArray(
            $this->client->sendRequest(
                $url,
                Interfaced::METHOD_POST,
                $inputs,
                [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
            )
        );
    }
}
