<?php
/**
 * Interface for comparable objects.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
interface Comparable
{
    /**
     * Determines whether an object is equal to another object. Since there are
     * no generics in PHP, the first test for equality should always be the
     * test for the same object type.
     *
     * @param mixed $other Other object to test equality against
     * @return bool
     */
    public function equals($other);
}
