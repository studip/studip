<?php

namespace Studip\Activity;


class CoursewareProvider implements ActivityProvider
{

    public function getActivityDetails($activity)
    {
        $structural_element = \Courseware\StructuralElement::find($activity->object_id);
        if (!$structural_element) {
            return false;
        }
        $payload = json_decode($structural_element['payload']);

        $activity->content = formatReady($payload['description']);

        if ($activity->context == "course") {
            $url =  \URLHelper::getURL('dispatch.php/course/courseware/?cid='). $activity->context_id . '#/structural_element/' . $structural_element->id;
            $activity->object_url = [
                $url => _('Zur Courseware in der Veranstaltung')
            ];
        } elseif ($activity->context == "user") {
            $url =  \URLHelper::getURL('dispatch.php/contents/my_contents'). '#/structural_element/' . $structural_element->id;
            $activity->object_url = [
                $url => _('Zur eigenen Courseware')
            ];
        }

        return true;
    }

    public static function getLexicalField()
    {
        return _('eine Courseware-Aktivität');
    }
}