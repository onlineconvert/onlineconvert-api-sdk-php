<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use OnlineConvert\Exception\FileNotExists;
use OnlineConvert\Exception\OnlineConvertSdkException;

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
     * @const string
     */
    const INPUT_TYPE_UPLOAD = 'upload';

    /**
     * @const string
     */
    const INPUT_TYPE_REMOTE = 'remote';

    /**
     * Shortcut to post inputs with different type.
     * The 'type' key inside the input array must be equal at the constants provided in this class.
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param array $input          array with the input information in format:
     *                              [
     *                              'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_UPLOAD,
     *                              'source' => '/your/source'
     *                              ]
     * @param array $job            if this is not defined will take the last one created
     *
     * @return array
     */
    public function postJobInput(array $input, array $job)
    {
        if ($input['type'] === self::INPUT_TYPE_UPLOAD) {
            return $this->postJobInputUpload($input['source'], $job['id'], $job['server'], $job['token']);
        }

        return $this->postJobInputRemote($input['source'], $job['id']);
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
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $source
     * @param string $jobId
     *
     * @return array
     */
    public function postJobInputRemote($source, $jobId)
    {
        $input = [
            'type'   => self::INPUT_TYPE_REMOTE,
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
     * @throws FileNotExists when the source do not exist.
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
}
