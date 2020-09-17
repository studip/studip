<?php
namespace Studip;

/**
 * Trait that handles the different method signatures in addFile() due to
 * changes in the Zip module since v1.18. This is the legacy part.
 */
trait ZipArchiveTrait
{
    /**
     * Adds a single file.
     *
     * @param String $filename Name of the file to add
     * @param String $localname Name of the file inside the archive,
     *                          will default to $filename
     * @param int $start Unused but required (according to php doc)
     * @param int $length Unused but required (according to php doc)
     * @return false on error, $localname otherwise
     */
    public function addFile($filename, $localname = null, $start = 0, $length = 0)
    {
        $localname = $this->convertLocalFilename($localname ?: basename($filename));
        return parent::addFile($filename, $localname, $start, $length)
            ? $localname
            : false;
    }
}
