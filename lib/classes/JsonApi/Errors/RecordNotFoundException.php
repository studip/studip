<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class RecordNotFoundException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $error = new Error($error ?: 'Not Found', null, 404);
        parent::__construct($error, 404);
    }
}
