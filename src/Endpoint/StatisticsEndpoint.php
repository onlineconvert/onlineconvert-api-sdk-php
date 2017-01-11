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
     * @param string $day
     *
     * @return array
     *
     * @throws OnlineConvertSdkException when error on the requests
     */
    public function getStatsByDay($day)
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_DAY, ['day' => $day]);

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
     * @param string $month
     *
     * @return array
     */
    public function getStatsByMonth($month)
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_MONTH, ['month' => $month]);

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
     * @param string $year
     *
     * @return array
     */
    public function getStatsByYear($year)
    {
        try {
            $url = $this->client->generateUrl(Resources::STATS_YEAR, ['year' => $year]);

            return $this->responseToArray(
                $this->client->sendRequest($url, OnlineConvertClient::METHOD_GET)
            );
        } catch (\Exception $e) {
            throw new OnlineConvertSdkException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
