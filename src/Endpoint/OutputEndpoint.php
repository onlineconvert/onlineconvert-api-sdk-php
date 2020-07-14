<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\Interfaced;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use OnlineConvert\Exception\OnlineConvertSdkException;
use OnlineConvert\Exception\OutputNotFound;
use OnlineConvert\Exception\RequestException;

/**
 * Manage Output Endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 * @author  Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class OutputEndpoint extends Abstracted
{
    /**
     * Get the list of outputs of a Job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJobOutputs($jobId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_OUTPUTS, ['job_id' => $jobId]);

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
     * Get a specific output of a Job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when error on the request
     *
     * @param string $jobId
     * @param string $outputId
     *
     * @return array
     */
    public function getJobOutput($jobId, $outputId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_OUTPUT_ID, ['job_id' => $jobId, 'output_id' => $outputId]);

        $response = $this->client->sendRequest(
            $url,
            Interfaced::METHOD_GET,
            null,
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return $this->responseToArray($response);
    }

    /**
     * Delete a specific output of a Job
     *
     * @api
     *
     * @throws OnlineConvertSdkException when the deletion was not successfully - request error
     *
     * @param string $jobId
     * @param string $outputId
     *
     * @return bool
     */
    public function deleteJobOutput($jobId, $outputId)
    {
        $url = $this->client->generateUrl(Resources::JOB_ID_OUTPUT_ID, ['job_id' => $jobId, 'output_id' => $outputId]);

        $this->client->sendRequest(
            $url,
            Interfaced::METHOD_DELETE,
            [],
            [Interfaced::HEADER_OC_JOB_TOKEN => $this->userToken]
        );

        return true;
    }

    /**
     * Download outputs from a job
     *
     * @throws OutputNotFound when the job have not outputs
     * @throws OnlineConvertSdkException when error on the download request
     *
     * @param array         $job
     * @param callable|null $progressFunction function to monitoring the progress of the download
     *
     * @return array when error on the request
     */
    public function downloadOutputs(array $job, callable $progressFunction = null)
    {
        $outputs = $job['output'];

        if (0 == count($outputs)) {
            throw new OutputNotFound(json_encode($job));
        }

        $outputPath = [];

        foreach ($outputs as $output) {
            $outputPath[$output['id']] = $this->downloadUrl($output['uri'], $output['id'], $progressFunction);
        }

        return $outputPath;
    }

    /**
     * Just provide a way to send the exact request and process a url download
     *
     * @throws RequestException on error in the download
     *
     * @param string        $url
     * @param string        $outputId
     * @param callable|null $progressFunction function to monitoring the progress of the download
     *
     * @return string
     */
    private function downloadUrl($url, $outputId, callable $progressFunction = null)
    {
        $saveTo = $this->client->getConfig()->downloadFolder . DIRECTORY_SEPARATOR . $outputId;

        try {
            if (!file_exists($saveTo)) {
                mkdir($saveTo);
            }
        } catch (\Exception $e) {
            $saveTo = $this->client->getConfig()->downloadFolder;
        }

        try {
            try {
                $headerRequest = $this->client->getClient()->request(
                    'HEAD',
                    $url
                );

                $contentDispositionHeader = $headerRequest->getHeader('Content-Disposition');

                if (isset($contentDispositionHeader[0])) {
                    preg_match("/filename=\"\s*(.*?)\s*\"/", $contentDispositionHeader[0], $filename);

                    if (isset($filename[1])) {
                        $saveTo .= DIRECTORY_SEPARATOR . $filename[1];
                    } else {
                        $saveTo .= DIRECTORY_SEPARATOR . $outputId;
                    }
                } else {
                    $saveTo .= DIRECTORY_SEPARATOR . $outputId;
                }
            } catch (\Exception $e) {
                //nothing to do
            }

            $this->client->getClient()->request(
                'GET',
                $url,
                [
                    'sink'     => $saveTo,
                    'progress' => $progressFunction,
                ]
            );
        } catch (GuzzleRequestException $e) {
            throw new RequestException($e->getMessage());
        }

        return $saveTo;
    }
}
