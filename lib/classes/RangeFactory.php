<?php
/**
 * Factory for ranges.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since Stud.IP 4.1
 */
final class RangeFactory
{
    /**
     * Create a range by given type and id.
     *
     * @param string $type Range type
     * @param mixed  $id   Range id
     * @return mixed any of the supported range types
     * @throws Exception when an invalid range type was given
     *
     * @todo Should this be more dynamic in case any more ranges are added?
     */
    public static function createRange($type, $id)
    {
        if ($type === 'user') {
            return new User($id);
        }

        if ($type === 'course') {
            return new Course($id);
        }

        if ($type === 'institute') {
            return new Institute($id);
        }

        throw new Exception('Unknown type');
    }
}
