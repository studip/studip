<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class AuthorizationFailedException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct()
    {
        $error = new Error('Forbidden', null, 403);
        parent::__construct($error, 403);
    }
}
