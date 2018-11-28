<?php

/**
 * Userfilter_FieldController
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

class Userfilter_FieldController extends AuthenticatedController
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
        PageLayout::setTitle(_('Bedingung'));
        PageLayout::addScript('studip-userfilter.js');
    }

    /**
     * Gets the configuration settings for a userfilter field. The type of the
     * field is set via the request.
     */
    public function configure_action()
    {
        $this->conditionFields = UserFilterField::getAvailableFilterFields();
        if ($className = Request::option('fieldtype')) {
            list($fieldType, $param) = explode('_', $className);
            $this->className = $className;
            $this->field = new $fieldType($param);
        }
    }
}
