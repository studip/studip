<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class UnsupportedRequestError extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error('Unsupported request.', null, 403, null, null, $detail, $source);
        parent::__construct($error, 403);
    }
}
