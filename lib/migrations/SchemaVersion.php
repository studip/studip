<?php
/**
 * SchemaVersion.php - schema version interface for migrations
 *
 * This interface provides an abstract way to retrieve and set the current
 * version of a schema. Implementations of this interface need to define
 * where the version information is actually stored (e.g. in a file).
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
interface SchemaVersion
{
    /**
     * Returns current schema version (as maximum number).
     *
     * @return int schema version
     */
    public function get();

    /**
     * Returns whether the given version is already present for the given
     * domain.
     *
     * @param  int $version Version number
     * @return bool
     */
    public function contains($version);

    /**
     * Adds a schema version.
     *
     * @param int $version schema version
     */
    public function add($version);

    /**
     * Removes a schema version.
     *
     * @param int $version schema version
     */
    public function remove($version);
}
