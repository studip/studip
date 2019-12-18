<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class BadRequestException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error('Bad Request', null, 400, null, null, $detail, $source);
        parent::__construct($error, 400);
    }
}
