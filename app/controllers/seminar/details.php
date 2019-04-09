<?php
/**
 * details.php - Seminar_DetailsController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */



class Seminar_DetailsController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        ModuleManagementModel::setLanguage($_SESSION['_language']);

        if (Request::isXhr()) {
            $this->set_layout(null);
        }
    }

    public function show_module_pathes_action($seminar_id)
    {
        $trail_classes = [
            'Modulteil',
            'StgteilabschnittModul',
            'StgteilAbschnitt',
            'StgteilVersion'];
        $this->mvv_pathes = MvvCourse::get($seminar_id)->getTrails($trail_classes);
    }

}