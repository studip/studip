<?php
# Lifter010: TODO
/**
 * DateFormater.class.php - Handles the formatting of one date and associated rooms.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      sbrummer <soenke.brummerloh@uni-osnabrueck.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Formates one SingleDate object or a series of SingleDate objects into a nice format. 
 */
class DateFormatter {
    /**
     * @var array holds the dates use for formatting
     */
    private $dates;
    
    /**
     * @var string holds the return-type, may be int or string 
     */
    private $return_mode;

    /**
     * @param $dates an array with an array of SingleDate objects.
     * @param string $return_mode expected values are 'int', 'string' and 'export'. The default value is 'string'.
     * @return void
     */
    private function __construct($dates, $return_mode = 'string')
    {
        $this->dates = $dates;
        $this->return_mode = $return_mode;
    }

    /**
     * Formats one single date into a nice format.
     * @static
     * @param $date SingleDate object
     * @param string $return_mode expected values are 'int', 'string' and 'export'. The default value is 'string'.
     * @return string
     */
    public static function formatDateAndRoom($date, $return_mode = 'string')
    {
        $dates = DateFormatter::wrapDateWithArray($date);
        return DateFormatter::formatDateWithAllRooms($dates, $return_mode);
    }

    /**
     * Formats a series of SingleDate objects into a nice format. The dates parameter is an array of dates.
     * The array has to have the key 'termin' with an array of SingleDate objects as value.
     * @static
     * @param  $dates an array with an array of SingleDate objects
     * @param string $return_mode expected values are 'int', 'string' and 'export'. The default value is 'string'.
     * @return string
     */
    public static function formatDateWithAllRooms($dates, $return_mode = 'string')
    {
        $dateFormatter = new DateFormatter($dates, $return_mode);
        return $dateFormatter->internalFormatDateWithAllRooms();
    }

    private static function wrapDateWithArray($date)
    {
        $dates = [];
        $dates['termin'] = [$date];
        return $dates;
    }

    private function internalFormatDateWithAllRooms()
    {
        $dateWithRooms = '';
        if ($this->dates['termin']) {
            // if we have multiple rooms at the same time we display them all
            foreach ($this->dates['termin'] as $num => $termin_id) {
                $date = new SingleDate($termin_id);

                // if we want an int and format the date ourself
                if ($this->return_mode == 'int') {
                    return $date->getStartTime();
                }

                $isFirstDate = ($num == 0);
                if ($isFirstDate) {
                    $dateWithRooms = $this->internalFormatDateAndRoom($date);                    
                } else {
                    $dateWithRooms .= ', ' . $this->formatRoom($date);
                }
            }
        }
        return $dateWithRooms;
    }

    private function internalFormatDateAndRoom($date)
    {
        $ret = $this->formatDate($date);

        if ($this->return_mode != 'int') {
            $formatedRooms = $this->formatRoom($date);
            if ($formatedRooms) {
                $ret .= ', ';
                $ret .= _("Ort:") . ' ';
                $ret .= $formatedRooms;
            }
        }

        return $ret;
    }

    private function formatDate($date)
    {
        if ($this->return_mode == 'int') {
            return $date->getStartTime();
        }
        else {
            return $date->toString();
        }
    }

    private function formatRoom($date)
    {
        if ($this->return_mode == 'int') {
            return '';
        }
        else {
            return $this->formatLocationText($date);
        }
    }

    private function formatLocationText($date)
    {
        if ($this->hasResource($date)) {
            $resObj = ResourceObject::Factory($date->getResourceID());
            return $this->generateLocationTextFromResourceObject($resObj);
        } else if ($this->hasFreeRoomText($date)) {
            return $this->generateLocationTextFromFreeRoomText($date);
        } else {
            return '';
        }
    }

    private function generateLocationTextFromResourceObject($resObj)
    {
        if ($this->return_mode == 'string') {
            return $resObj->getFormattedLink(TRUE, TRUE, TRUE);
        }
        else {
            return htmlReady($resObj->getName());
        }
    }

    private function generateLocationTextFromFreeRoomText($date)
    {
        return '(' . htmlReady($date->getFreeRoomText()) . ')';
    }

    private function hasResource($date)
    {
        return $date && $date->getResourceID();
    }

    private function hasFreeRoomText($date)
    {
        return $date && $date->getFreeRoomText();
    }
}
