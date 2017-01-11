<?php
namespace OnlineConvert\Endpoint;

use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Exception\OnlineConvertSdkException;

/**
 * Manage Statistics endpoint
 *
 * @package OnlineConvert\Endpoint
 *
 * @author Luca Carboni <l.carboni@qaamgo.com>
 */
class StatisticsEndpoint extends Abstracted
{
    /**
     * Returns statistics info for a specific day passed in the format yyyy-mm-dd
     *
     * @param string $day    The day to search statistics for in the format yyyy-mm-dd
     * @param string $filter Can be 'single'|'all'. In this case, statistics for all of the user's API keys are returned
     *
     * @return array
     *
     * @throws OnlineConvertSdkException when error on the requests
     */
    public function getStatsByDay($day, $filter = 'single')
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_DAY . '/' . $filter, ['day' => $day]);

            return $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns statistics info for a specific month passed in the format yyyy-mm
     *
     * @param string $month  The month to search statistics for in the format yyyy-mm
     * @param string $filter Can be 'single'|'all'. In this case, statistics for all of the user's API keys are returned
     *
     * @return array
     */
    public function getStatsByMonth($month, $filter = 'single')
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_MONTH . '/' . $filter, ['month' => $month]);

            return $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns statistics info for a specific year passed in the format yyyy
     *
     * @param string $year   The year to search statistics for in the format yyyy
     * @param string $filter Can be 'single'|'all'. In this case, statistics for all of the user's API keys are returned
     *
     * @return array
     */
    public function getStatsByYear($year, $filter = 'single')
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_YEAR . '/' . $filter, ['year' => $year]);

            return $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
