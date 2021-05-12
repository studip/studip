<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Instance;
use Courseware\StructuralElement;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;

trait CoursewareInstancesHelper
{
    private function findInstance(string $instanceId): Instance
    {
        list($rangeType, $rangeId) = explode('_', $instanceId);

        return $this->findInstanceWithRange($rangeType, $rangeId);
    }

    private function findInstanceWithRange(string $rangeType, string $rangeId): Instance
    {
        $methods = [
            'course' => 'getCoursewareCourse',
            'courses' => 'getCoursewareCourse',
            'user' => 'getCoursewareUser',
            'users' => 'getCoursewareUser',
        ];
        if (!($method = $methods[$rangeType])) {
            throw new BadRequestException('Invalid range type: "' . $rangeType . '".');
        }
        if (!($root = StructuralElement::$method($rangeId))) {
            throw new RecordNotFoundException();
        }

        return new Instance($root);
    }
}
