<?php

namespace Qaamgo\Validator;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

/**
 * Class Options
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class Options implements Interfaced
{
    protected $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }
    public function validate($data, $constraints)
    {
        $retriever = new UriRetriever();
        $schema = $retriever->retrieve('file://'.$constraints);

        $this->validator->check($data, $schema);

        if ($this->validator->isValid()) {
            return true;
        }

        throw new NoValidOptionsException('Options no valid '. print_r($data, $constraints));
    }
}
