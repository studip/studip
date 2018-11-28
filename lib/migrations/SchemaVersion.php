<?php
# Lifter007: TEST

/**
 * SchemaVersion.php - schema version interface for migrations
 *
 * This interface provides an abstract way to retrieve and set the current
 * version of a schema. Implementations of this interface need to define
 * where the version information is actually stored (e.g. in a file).
 *
 * @author Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 * @license GPL2 or any later version
 * @package migrations
 */
class SchemaVersion
{
    /**
     * Returns current schema version.
     *
     * @return int schema version
     */
    public function get()
    {
        trigger_error(
            sprintf('%s() must be overridden.', __METHOD__),
            E_USER_ERROR
        );
    }


    /**
     * Sets the new schema version.
     *
     * @param int $version new schema version
     */
    public function set($version)
    {
        trigger_error(
            sprintf('%s() must be overridden.', __METHOD__),
            E_USER_ERROR
        );
    }
}
