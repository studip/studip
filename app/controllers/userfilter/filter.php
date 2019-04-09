<?php

/**
 * Userfilter_FilterController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class Userfilter_FilterController extends AuthenticatedController
{
    /**
     * @see AuthenticatedController::before_filter
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if ($GLOBALS['perm']->have_perm('admin') || ($GLOBALS['perm']->have_perm('dozent') && get_config('ALLOW_DOZENT_COURSESET_ADMIN'))) {
            Navigation::activateItem('/tools/coursesets');;
        }
        $this->conditionFields = UserFilterField::getAvailableFilterFields();

        PageLayout::setTitle(_('Auswahlbedingungen'));
        PageLayout::addScript('studip-userfilter.js');
    }

    /**
     * Show configuration for a given UserFilter.
     *
     * @param String $containerId Target HTML element
     * @param String $conditionId ID of an existiting UserFilter object
     */
    public function configure_action($containerId, $conditionId = '')
    {
        $this->containerId = $containerId;
        if ($conditionId) {
            $this->condition = new UserFilter($conditionId);
        }
    }

    /**
     * Adds a condition.
     */
    public function add_action()
    {
        $condition = new UserFilter();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');
        $data = [];
        for ($i=0 ; $i<sizeof($fields) ; $i++) {
            $current = $fields[$i];
            if ($this->conditionFields[$current]) {
                list($fieldType, $param) = explode('_', $current);
                $field = new $fieldType($param);
                $field->setCompareOperator($compareOps[$i]);
                $field->setValue($values[$i]);
                $condition->addField($field);
                $condition->show_user_count = true;
            }
        }
        $this->condition = $condition;
    }

    /**
     * Deletes the given UserFilter object.
     *
     * @param String $conditionId the UserFilter to delete.
     */
    public function delete_action($conditionId)
    {
        $condition = new UserFilter($conditionId);
        $condition->delete();
        $this->render_nothing();
    }

}
