<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DummyNavigation extends \Navigation implements \ArrayAccess
{
    /**
     * Return the list of subnavigation items of this object.
     */
    public function getSubNavigation()
    {
        return $this;
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this;
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * ArrayAccess: Delete the value at the given offset.
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * IteratorAggregate: Create interator for request parameters.
     */
    public function getIterator()
    {
        return new \ArrayIterator();
    }
}

class StudipMockNavigation
{
    public function __invoke(Request $request, Response $response, $next)
    {
        \Navigation::setRootNavigation(new DummyNavigation('stuff'));

        return $next($request, $response);
    }
}
