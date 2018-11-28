<?php
/**
 * This object represents a stored file reference in Stud.IP. A file reference
 * may refer to a physical file, a url or a remote file.
 *
 * @license GPL2 or any later version
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class StoredFileRef extends StoredFile
{
    protected $file_ref;

    /**
     * @param FileRef $ref
     */
    public function __construct(FileRef $ref)
    {
        $this->file_ref = $ref;
    }

    /**
     * Returns the name of the file referenced.
     *
     * @return string
     */
    public function getName()
    {
        return $this->file_ref->name;
    }

    /**
     * Returns the content of the file referenced.
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->file_ref->file->storage !== 'disk') {
            throw new Exception('How to handle this?');
        }
        return file_get_contents($this->file_ref->file->path);
    }
}
