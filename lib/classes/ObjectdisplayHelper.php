<?php

/**
 * ObjectdisplayHelper - utilityfunctions for object display
 *
 * Helps to output name with a link to the object.
 * Works for User and Course Objects
 *
 * ::link($object) produces the name with a link
 * ::avatarlink($object) produces the avatar and the name with a link
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class ObjectdisplayHelper {

    /**
     * Produces the name with a link to the given object
     * @param User/Course The given object
     * @return string html code
     */
    public static function link($object) {
        return '<a href="' . self::map($object, 'link') . '">' . self::map($object, 'name') . '</a>';
    }

    /**
     * Produces the avatar and the name with a link to the given object
     * @param User/Course The given object
     * @return string html code
     */
    public static function avatarlink($object) {
        return '<a href="' . self::map($object, 'link') . '">' . self::map($object, 'avatar') . " " . self::map($object, 'name') . '</a>';
    }

    /**
     * Mapping function where to find what
     * @param type $object the object
     * @param type $function the called function
     * @return string output
     */
    private static function map($object, $function) {

        /**
         * If you want to add an object to the helper simply add to this array
         */
        $mapping = array(
            'User' => array(
                'link' => function($obj) {
            return URLHelper::getLink('dispatch.php/profile', array('username' => $obj->username));
        },
                'name' => function($obj) {
            return htmlReady($obj->getFullname());
        },
                'avatar' => function($obj) {
            return Avatar::getAvatar($obj->id, $obj->username)->getImageTag(Avatar::SMALL,array('title' => htmlReady($obj->getFullname('no_title'))));
        }
            ),
            'Course' => array(
                'link' => function($obj) {
            return URLHelper::getLink('seminar_main.php', array('auswahl' => $obj->id));
        },
                'name' => function($obj) {
            return htmlReady($obj->name);
        },
                'avatar' => function($obj) {
            return CourseAvatar::getAvatar($obj->id)->getImageTag($size = CourseAvatar::SMALL,array('title' => htmlReady($obj->name)));
        }
            )
        );

        /*
         * Some php magic to call the right function if it exists
         */
        if ($object && $mapping[get_class($object)]) {
            return $mapping[get_class($object)][$function]($object);
        }
        return "";
    }

}
