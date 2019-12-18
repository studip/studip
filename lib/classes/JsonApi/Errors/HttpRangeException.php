<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class HttpRangeException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $error = new Error($error ?: 'Requested Range Not Satisfiable.', null, 416);
        parent::__construct($error, 416);
    }
}
