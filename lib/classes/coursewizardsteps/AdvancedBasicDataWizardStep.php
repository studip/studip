<?php
/**
 * AdvancedBasicDataWizardStep.php
 * Course wizard step for getting the basic course data.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author      Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdvancedBasicDataWizardStep extends BasicDataWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which nqumber has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        $values = $this->adjustValues($values);

       // We only need our own stored values here.
        if ($values[__CLASS__]['studygroup']) {
            return parent::getStepTemplate($values, $stepnumber, $temp_id);
        }

        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'].'/app/views/course/wizard/steps');
        $template = $factory->open('advancedbasicdata/index');
        if ($this->setupTemplateAttributes($template, $values, $stepnumber, $temp_id)) {
            return $template->render();
        }
    }

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
        $values = $this->adjustValues($values);
        return parent::validate($values);
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
        $values = $this->adjustValues($values);
        $course = parent::storeValues($course, $values);

        // There probably was an error upon storing
        if (!$course) {
            return false;
        }

        // Studygroup? -> nothing to do here
        if ($values[__CLASS__]['studygroup']) {
            return $course;
        }

        // Add advanced data
        $course->Untertitel = $values[__CLASS__]['subtitle'];
        $course->art = $values[__CLASS__]['kind'];
        $course->ects = $values[__CLASS__]['ects'];
        $course->admission_turnout = $values[__CLASS__]['maxmembers'];
        if ($course->store() === false) {
            PageLayout::postError(sprintf(_('Es ist ein Fehler beim Speichern der erweiterten Einstellungen für %s aufgetreten. Kontrollieren Sie bitte:')
                    , htmlReady($course->name)),
                    [_('Untertitel der Veranstalung'),
                        _('Art der Veranstaltung'),
                        _('ECTS-Punkte der Veranstaltung'),
                        _('Max. Teilnehmendenzahl der Veranstaltung')]);
        }
        return $course;
    }

    /**
     * This method will adjust the given values from parent class
     * or use previously set values from this class.
     *
     * @param array $values Array of values
     * @return array of adjusted values
     */
    private function adjustValues($values)
    {
        $parent_class = get_parent_class($this);

        if (!isset($values[__CLASS__]) && isset($values[$parent_class])) {
            $values[__CLASS__] = $values[$parent_class];
        } else {
            $values[$parent_class] = $values[__CLASS__];
        }

        return $values;
    }

    /**
     * Copy values for basic data wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $values = parent::copy($course, $values);
        $values = $this->adjustValues($values);

        $values[__CLASS__] = array_merge($values[__CLASS__], [
            'subtitle' => $course->untertitel,
            'kind' => $course->art,
            'ects' => $course->ects,
            'maxmembers' => $course->admission_turnout,
        ]);

        return $values;
    }
}
