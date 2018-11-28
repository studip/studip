<?php
/**
 * This object represents a stored physical file in Stud.IP. A file in this
 * case consists of a name and the path/location of the file.
 *
 * @license GPL2 or any later version
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class StoredFilePath extends StoredFile
{
    protected $file_path;

    /**
     * @param string $file_name
     * @param string $file_path
     */
    public function __construct($file_name, $file_path)
    {
        $this->file_name = $file_name;
        $this->file_path = $file_path;
    }

    /**
     * Retrieves and returns the actual file contents.
     *
     * @return string
     */
    public function getContent()
    {
        if (!file_exists($this->file_path)) {
            throw new Exception('File path is invalid');
        }
        if (!is_readable($this->file_path)) {
            throw new Exception('File path is not readable');
        }

        return file_get_contents($this->file_path);
    }
}
