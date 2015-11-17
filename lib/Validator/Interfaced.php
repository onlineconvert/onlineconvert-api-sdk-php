<?php

namespace Qaamgo\Validator;


/**
 * Interface Interfaced
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
interface Interfaced
{
    public function validate($data, $constraints);
}