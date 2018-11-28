<?php
/**
 * This object represents a general stored file in Stud.IP. A file in this
 * case consists of a name and the actual file contents.
 *
 * @license GPL2 or any later version
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class StoredFile
{
    protected $file_name;
    protected $content;

    /**
     * @param string $file_name
     * @param string $content
     */
    public function __construct($file_name, $content)
    {
        $this->file_name = $file_name;
        $this->content   = $content;
    }

    /**
     * Returns the name of the file.
     *
     * @return string
     */
    public function getName()
    {
        return $this->file_name;
    }

    /**
     * Returns the file's contents.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Stores the file's contents at the given location.
     *
     * @param string $location Folder to store the file in
     */
    public function store($location)
    {
        $location = rtrim($location, '/');

        if (!is_dir($location)) {
            throw new Exception('Given location is not a folder');
        }
        if (!is_writable($location)) {
            throw new Exception('Cannot write to given location');
        }

        $file_name = $location . '/' . ltrim($this->getName(), '/');
        file_put_contents($file_name, $this->getContent());
    }
}
