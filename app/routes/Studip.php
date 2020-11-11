<?php
namespace RESTAPI\Routes;

use Config;
use SemClass;
use SemType;

/**
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class Studip extends \RESTAPI\RouteMap
{
    /**
     * Grundlegende Systemeinstellungen
     *
     * @get /studip/settings
     */
    public function getSettings()
    {
        $sem_types = array_map(function ($item) {
            return [
                'name'  => $item['name'],
                'class' => $item['class'],
            ];
        }, SemType::getTypes());

        $sem_classes = array_map(function ($item) {
            $item = (array) $item;
            return reset($item);
        }, SemClass::getClasses());

        return [
            'ALLOW_CHANGE_USERNAME' => Config::get()->ALLOW_CHANGE_USERNAME,
            'ALLOW_CHANGE_EMAIL'    => Config::get()->ALLOW_CHANGE_EMAIL,
            'ALLOW_CHANGE_NAME'     => Config::get()->ALLOW_CHANGE_NAME,
            'ALLOW_CHANGE_TITLE'    => Config::get()->ALLOW_CHANGE_TITLE,
            'INST_TYPE'             => $GLOBALS['INST_TYPE'],
            'SEM_TYPE'              => $sem_types,
            'SEM_CLASS'             => $sem_classes,
            'TERMIN_TYP'            => $GLOBALS['TERMIN_TYP'],
            'PERS_TERMIN_KAT'       => $GLOBALS['PERS_TERMIN_KAT'],
            'SUPPORT_EMAIL'         => $GLOBALS['UNI_CONTACT'],
            'TITLES'                => $GLOBALS['DEFAULT_TITLE_FOR_STATUS'],
            'UNI_NAME_CLEAN'        => Config::get()->UNI_NAME_CLEAN,
        ];
    }

    /**
     * Farbeinstellungen
     *
     * @get /studip/colors
     */
    public function getColors()
    {
        // TODO: Move these definitions somewhere else (but where!?)
        return [
            'background' => '#e1e4e9',
            'dark'       => '#34578c',
            'light'      => '#899ab9',
        ];
    }
}
