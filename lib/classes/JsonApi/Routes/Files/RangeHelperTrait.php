<?php

namespace JsonApi\Routes\Files;

trait RangeHelperTrait
{
    private function validateResourceType($type)
    {
        return in_array($type, ['courses', 'institutes', 'users']);
    }

    private function findResource($type, $resourceId)
    {
        $resource = null;

        switch ($type) {
            case 'courses':
                $resource = \Course::find($resourceId);
                break;

            case 'institutes':
                $resource = \Institute::find($resourceId);
                break;

            case 'users':
                $resource = \User::find($resourceId);
                break;
        }

        return $resource;
    }
}
