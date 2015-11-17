<?php
/**
 * Created by PhpStorm.
 * User: andres
 * Date: 17/11/2015
 * Time: 22:49
 */

namespace Qaamgo\Job;

use Qaamgo\Models\InputFile;

class Async extends  Sync implements Interfaced
{

    public function createAsyncJob($category, $target, $input, $callback, $options = [])
    {
        $inputFile = new InputFile();
        $inputFile->source = $input;

        $this->filterInput($inputFile, $input);

        $this->conversion->category = $category;
        $this->conversion->target = $target;

        $this->job->callback = $callback;

        $this->validateOptions($options, $this->conversion->category, $this->conversion->target);

        $this->createJobForApi();

        return $this->getJobInfo($this->createdJob->id);
    }

}
