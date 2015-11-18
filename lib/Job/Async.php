<?php

namespace Qaamgo\Job;

use Qaamgo\Models\InputFile;

class Async extends  Sync implements Interfaced
{

    /**
     * Create a job that will send a notification.
     *
     * @param $category
     * @param $target
     * @param $input
     * @param string $callbackUrl the url where the notification will be sent
     * @param array $options
     * @return mixed
     */
    public function createAsyncJob($category, $target, $input, $callbackUrl, $options = [])
    {
        $inputFile = new InputFile();
        $inputFile->source = $input;

        $this->filterInput($inputFile, $input);

        $this->conversion->category = $category;
        $this->conversion->target = $target;

        $this->job->callback = $callbackUrl;

        $this->validateOptions($options, $this->conversion->category, $this->conversion->target);

        $this->createJobForApi();

        return $this->getJobInfo($this->createdJob->id);
    }

}
