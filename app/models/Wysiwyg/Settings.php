<?php
/**
 * Settings.php - WYSIWYG editor settings.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @package     Wysiwyg
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.2
 * @author      Robert Costa <rcosta@uos.de>
 */
namespace Studip\Wysiwyg;

/**
 * WYSIWYG editor settings.
 */
class Settings extends Singleton
{
    /**
     * Check whether WYSIWYG has been disabled by user or
     * global settings.
     *
     * @return boolean `true` if WYSIWYG is disabled.
     */
    public function isDisabled() {
        return (boolean)$GLOBALS['user']->cfg->WYSIWYG_DISABLED
            || $this->isGloballyDisabled();
    }

    /**
     * Check whether the admin has globally disabled WYSIWYG
     * for this Stud.IP installation.
     *
     * @return boolean `true` if WYSIWYG is globally disabled.
     */
    public function isGloballyDisabled() {
        return !\Config::get()->WYSIWYG;
    }

    /**
     * Return all settings in JSON representation.
     *
     * @return string JSON representation of all settings.
     */
    public function asJson() {
        $settings = new \stdClass;
        $settings->global = new \stdClass;
        $settings->global->disabled = !\Config::get()->WYSIWYG;
        return json_encode($settings);
    }
}

