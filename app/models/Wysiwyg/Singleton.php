<?php
/**
 * Singleton.php - Base class for singletons.
 *
 * This class could be used for any singletons but is created in
 * the WYSIWYG namespace since it is not part of Stud.IP's API.
 *
 * The original implementation was copied from the design
 * patterns section of Josh Lockhart's "PHP: The Right Way"
 * and is licensed under a Creative Commons Attribution-
 * NonCommercial-ShareAlike 3.0 Unported License.
 *
 * http://www.phptherightway.com/pages/Design-Patterns.html
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @since       File available since Release 3.2
 * @author      Robert Costa <rcosta@uos.de>
 */
namespace Studip\Wysiwyg;

/**
 * Base class for singletons.
 */
class Singleton
{
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instance
     *                                of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance
     * of the *Singleton* via the `new` operator from outside of
     * this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the
     * *Singleton*'s instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of
     * a *Singleton* instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}

