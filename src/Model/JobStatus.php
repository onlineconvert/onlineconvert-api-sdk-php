<?php

namespace OnlineConvert\Model;

use OnlineConvert\Exception\StatusUnknownException;

/**
 * Class Status
 *
 * @package OnlineConvert\Model
 */
class JobStatus
{
    /**
     * Status when the job is incomplete waiting for information to be ready or processed
     *
     * @const string
     */
    const STATUS_INCOMPLETE = 'incomplete';

    /**
     * Status when the job is ready to begin process
     *
     * @const string
     */
    const STATUS_READY = 'ready';

    /**
     * Status when the job is downloading the input files
     *
     * @const string
     */
    const STATUS_DOWNLOADING = 'downloading';

    /**
     * Status when the job is processing the job
     *
     * @const string
     */
    const STATUS_PROCESSING = 'processing';

    /**
     * Status when the job fails
     *
     * @const string
     */
    const STATUS_FAILED = 'failed';

    /**
     * Status when the job completes correctly
     *
     * @const string
     */
    const STATUS_COMPLETED = 'completed';

    /**
     * The status ranking list.
     * A status can be updated only if the new one is at the same or at a higher ranking level.
     *
     * @var array
     */
    private $statusesRanking = [
        self::STATUS_INCOMPLETE  => 1,
        self::STATUS_READY       => 2,
        self::STATUS_DOWNLOADING => 2,
        self::STATUS_PROCESSING  => 3,
        self::STATUS_FAILED      => 4,
        self::STATUS_COMPLETED   => 5,
    ];

    /**
     * The status code
     *
     * @var string
     */
    private $code;

    /**
     * The status ranking level
     *
     * @var integer
     */
    private $rank;

    /**
     * Status constructor.
     *
     * @param string $statusCode
     */
    public function __construct($statusCode)
    {
        if (empty($this->statusesRanking[$statusCode])) {
            throw new StatusUnknownException('Unknown status: ' . $statusCode);
        }

        $this->code = $statusCode;
        $this->rank = $this->statusesRanking[$statusCode];
    }

    /**
     * Checks if the job status can be updated with the passed one
     *
     * @param JobStatus $newStatus    The new desired status
     *
     * @return boolean                True when it is possible to update the actual status with the passed one
     *
     * @throws StatusUnknownException When one of the passed statuses is unknown
     */
    public function canBeUpdated(JobStatus $newStatus)
    {
        return ($newStatus->getRank() >= $this->rank);
    }

    /**
     * Checks if the status is equal to the one passed (one of self::STATUS_*)
     *
     * @param string $status
     *
     * @return boolean
     */
    public function isStatus($status)
    {
        return ($this->code === $status);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }
}
