<?php
/**
 * CourseWizardStep.php
 * Interface definition for classes that can act as step in the
 * course creation wizard.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

interface CourseWizardStep
{
    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values);

    /**
     * This method provides the possibility to have buttons in the form
     * that do something other than just iterating between the wizard
     * steps, e.g. QuickSearch or fallbacks for Non-JS which allow partial
     * form loading and continuing if certain buttons are clicked for value
     * submission
     * An example would be a button submitting a chosen institute ID,
     * thus allowing a No-JS construction of a QuickSearch needing this
     * institute ID for its own purposes.
     *
     * @param Array $values former values from the wizard.
     * @return new altered $values. If nothing changes return $values.
     */
    public function alterValues($values);

    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id);

    /**
     * Validates entered data, e.g. if all mandatory values have been given.
     *
     * @param $values
     * @return mixed
     */
    public function validate($values);

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The given course object with updated values.
     */
    public function storeValues($course, $values);

    /**
     * Prepares a copy of the given course by setting the necessary values
     * in the given array.
     *
     * @param Course $course the course to copy
     * @param Array $values values to set for course wizard
     * @return Array original values array with added entries for this step.
     */
    public function copy($course, $values);

}