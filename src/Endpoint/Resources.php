<?php
namespace OnlineConvert\Endpoint;

/**
 * Resources used by the endpoints
 *
 * @package OnlineConvert\Client
 */
class Resources
{
    /**
     * GET lis of the valid statuses
     *
     * @const string
     */
    const STATUSES = 'statuses';

    /**
     * GET a list of the valid conversions
     *
     * @const string
     */
    const CONVERSIONS = 'conversions';

    /**
     * GET list of the jobs for a API KEY
     * POST a new job
     *
     * @const string
     */
    const JOB = 'jobs';

    /**
     * GET job information
     * DELETE a job that haven't started
     * PATCH a job to start
     *
     * @const string
     */
    const JOB_ID = 'jobs/{job_id}';

    /**
     * GET list of the threads defined for a job
     *
     * @const string
     */
    const JOB_ID_THREADS = 'jobs/{job_id}/threads';

    /**
     * GET the change history for a job
     *
     * @const string
     */
    const JOB_ID_HISTORY = 'jobs/{job_id}/history';

    /**
     * GET list of conversions for a job
     * POST a conversion to a job
     *
     * @const string
     */
    const JOB_ID_CONVERSIONS = 'jobs/{job_id}/conversions';

    /**
     * GET the conversion of a job
     * DELETE the conversion of a job
     *
     * @const string
     */
    const JOB_ID_CONVERSION_ID = 'jobs/{job_id}/conversions/{conversion_id}';

    /**
     * GET list of the inputs of a job
     * POST a input to a job
     *
     * @const string
     */
    const JOB_ID_INPUTS = 'jobs/{job_id}/input';

    /**
     * GET the input information of a job
     * DELETE the input of a job
     *
     * @const string
     */
    const JOB_ID_INPUT_ID = 'jobs/{job_id}/input/{input_id}';

    /**
     * GET the outputs of a job
     *
     * @const string
     */
    const JOB_ID_OUTPUTS = 'jobs/{job_id}/output';

    /**
     * GET a output of a job
     * DELETE a output of a job
     *
     * @const string
     */
    const JOB_ID_OUTPUT_ID = 'jobs/{job_id}/output/{output_id}';

    /**
     * GET the api schema
     *
     * @const string
     */
    const GET_SCHEMA = 'schema';

    /**
     * GET statistics info for a specific day passed in the format yyyy-mm-dd
     *
     * @const string
     */
    const STATS_DAY = 'stats/day/{day}';

    /**
     * GET statistics info for a specific month passed in the format yyyy-mm
     *
     * @const string
     */
    const STATS_MONTH = 'stats/month/{month}';

    /**
     * GET statistics info for a specific year passed in the format yyyy
     *
     * @const string
     */
    const STATS_YEAR = 'stats/year/{year}';

    /**
     * POST file
     *
     * @const string
     */
    const URL_POST_FILE = '{server}/upload-file/{job_id}';

    /**
     * GET presets
     */
    const URL_PRESETS = '/presets';

    /**
     * GET single preset
     */
    const URL_PRESETS_GET = '/presets/{preset_id}';

    /**
     * Update a single preset
     */
    const URL_PRESETS_PATCH = '/presets/{preset_id}';

    /**
     * DELETE presets
     */
    const URL_PRESETS_DELETE = '/presets/{preset_id}';
}
