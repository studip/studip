<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class NotImplementedException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error('Not Implemented Error', null, 501, null, null, $detail, $source);
        parent::__construct($error, 501);
    }
}
