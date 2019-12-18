<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class ConflictException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $error = new Error($error ?: 'Conflict', null, 409);
        parent::__construct($error, 409);
    }
}
