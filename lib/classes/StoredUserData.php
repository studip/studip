<?php
/**
 * This class is used to transport all data a plugin might have stored for a
 * specific user.
 * Stored data may be a number of files, tabular data or urls.
 *
 * @license GPL2 or any later version
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class StoredUserData
{
    public $user_id;

    protected $data = [
        'file'    => [],
        'tabular' => [],
    ];

    /**
     * Construct a storage object for a specific user.
     *
     * @param User $user User object
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Returns the associated user.
     *
     * @return User object
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns whether this object actually contains any data.
     *
     * @return bool
     */
    public function hasData()
    {
        return array_sum(array_map('count', $this->data)) > 0;
    }

    /**
     * Adds tabular data under the specified key. By passing an optional
     * context, the data may be associated with this context.
     *
     * @param string      $name Display label
     * @param string      $key Table name (e.g. database table)
     * @param array       $value Array containing the rows
     * @param SimpleORMap $context Optional context
     */
    public function addTabularData($name, $key, array $value, SimpleORMap $context = null)
    {
        if ($value) {
            $this->addData('tabular', compact('name', 'key', 'value'), $context);
        }
    }

    /**
     * Adds a file reference. By passing an optional context, the data may be
     * associated with this context.
     *
     * @param FileRef     $fileref
     * @param SimpleORMap $context Optional context
     */
    public function addFileRef(FileRef $fileref, SimpleORMap $context = null)
    {
        if ($fileref->file->getURL()) {
            $this->addFileWithContents(
                $fileref->name . '.url',
                "[InternetShortcut]\nURL={$fileref->file->getURL()}\n",
                $context
            );
        } else if ($fileref->file->getPath()) {
            // add folder structure to zip
            $folder = Folder::find($fileref->folder_id);
            $zipDir = $folder->range_type . "/";
            if($folder->parent_id){                
                foreach($folder->getParents() as $parent){
                    $zipDir .= ($parent->name) ?  $parent->name . "/" : ""  ;
                }
            }
            $this->addFileAtPath($fileref->name, $fileref->file->getPath(),$zipDir, $context);
        }
    }

    /**
     * Adds a local file on disk. By passing an optional context, the data may be
     * associated with this context.
     *
     * @param string      $name File name
     * @param string      $path File path
     * @param string      $zipDir Optional Zip File Path Structure 
     * @param SimpleORMap $context Optional context
     */
    public function addFileAtPath($name, $path, $zipDir = null, SimpleORMap $context = null)
    {
        $this->addData('file', compact('name', 'path', 'zipDir'), $context);
    }

    /**
     * Adds content as a file. By passing an optional context, the data may be
     * associated with this context.
     *
     * @param string      $name File name
     * @param string      $contents File contents (text or binary)
     * @param SimpleORMap $context Optional context
     */
    public function addFileWithContents($name, $contents, SimpleORMap $context = null)
    {
        $this->addData('file', compact('name', 'contents'), $context);
    }

    /**
     * Returns the stored file data for all contexts (if $context is null)
     * or a specific context.
     *
     * @param SimpleORMap $context Optional context
     * @return array
     */
    public function getFileData(SimpleORMap $context = null)
    {
        return $this->getData('file', $context);
    }

    /**
     * Returns the stored tabular data for all contexts (if $context is null)
     * or a specific context.
     *
     * @param SimpleORMap $context Optional context
     * @return array
     */
    public function getTabularData(SimpleORMap $context = null)
    {
        return $this->getData('tabular', $context);
    }

    /**
     * Adds stored data. By passing an optional context, the data may be
     * associated with this context.
     *
     * @param string      $type    Type of data
     * @param mixed       $data
     * @param SimpleORMap $context Optional context
     */
    protected function addData($type, $data, SimpleORMap $context = null)
    {
        if (!isset($this->data[$type])) {
            throw new InvalidArgumentException('Invalid data type');
        }

        $this->data[$type][] = $data + compact('context');
    }

    /**
     * Returns the stored data of the given type for all contexts
     * (if $context is null) or a specific context.
     *
     * @param string      $type    Type of data
     * @param SimpleORMap $context Optional context
     * @return array
     */
    protected function getData($type, SimpleORMap $context = null)
    {
        if (!isset($this->data[$type])) {
            throw new InvalidArgumentException('Invalid data type');
        }

        $data = $this->data[$type];

        if ($context) {
            $data = array_filter($data, function ($item) use ($context) {
                return $item['context'] instanceof $context && $item['context']->id == $context->id;
            });
        }

        return $data;
    }
}
