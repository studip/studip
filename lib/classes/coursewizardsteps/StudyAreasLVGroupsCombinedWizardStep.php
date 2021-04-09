<?php
/**
 * StudyAreasWizardStep.php
 * Course wizard step for assigning study areas.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StudyAreasLVGroupsCombinedWizardStep extends StudyAreasWizardStep
{

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        $ok = true;
        $errors = [];

        $coursetype = 1;
        foreach ($values[get_class($this)] as $class) {
            if (!empty($class['coursetype'])) {
                $coursetype = $class['coursetype'];
                break;
            }
        }
        $category = SeminarCategories::GetByTypeId($coursetype);
        if ($category->module) {
            if (isset($values['LVGroupsWizardStep'])
                    && !count($values['LVGroupsWizardStep']['lvgruppe_selection']['areas'])
                    && !count($values[get_class($this)]['studyareas'])
                    && $values[get_class($this)]['step'] > $values['LVGroupsWizardStep']['step']) {
                $ok = false;
                $errors[] = _('Die Veranstaltung muss mindestens einem Studienbereich oder einer LV-Gruppe zugeordnet sein.');
            }
        } else {
            if (!$values[get_class($this)]['studyareas']) {
                $ok = false;
                $errors[] = _('Die Veranstaltung muss mindestens einem Studienbereich zugeordnet sein.');
            }
        }
        if ($errors) {
            PageLayout::postMessage(MessageBox::error(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors));
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        // get courstype configuration to check
        // whether assignment of lv-gruppen is possible
        $coursetype = 1;
        foreach ($values as $class) {
            if (!empty($class['coursetype'])) {
                $coursetype = $class['coursetype'];
                break;
            }
        }
        $category = SeminarCategories::GetByTypeId($coursetype);
        $areas_required = true;
        if ($category->module
            && $values['LVGroupsWizardStep']['lvgruppe_selection']['areas'])
        {
            $areas_required = false;
        }
        if ($areas_required
            || ($values[get_class($this)]['studyareas'] && is_array($values[__CLASS__]['studyareas'])))
        {
            $course->study_areas = SimpleORMapCollection::createFromArray(
                StudipStudyArea::findMany($values[get_class($this)]['studyareas'])
            );
            if ($course->store()) {
                return $course;
            } else {
                return false;
            }
        }
        return $course;
    }

}
