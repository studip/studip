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
    protected $data = [
        'file'    => [],
        'tabular' => [],
        'url'     => [],
    ];

    protected $contexts = [
        'Course'    => [],
        'Institute' => [],
        'User'      => [],
    ];

    /**
     * Construct a storage object for a specific user.
     *
     * @param User $user User object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the associated user.
     *
     * @return User object
     */
    public function getUser()
    {
        return $this->user;
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
     * Adds a file. By passing an optional context, the data may be associated
     * with this context.
     *
     * @param StoredFile  $file
     * @param SimpleORMap $context Optional context
     */
    public function addFile(StoredFile $file, SimpleORMap $context = null)
    {
        $this->addStoredData('file', $file, $context);
    }

    /**
     * Adds tabular data under the specified key. By passing an optional
     * context, the data may be associated with this context.
     *
     * @param string      $key
     * @param mixed       $value
     * @param SimpleORMap $context Optional context
     */
    public function addTabularData($key, $value, SimpleORMap $context = null)
    {
        $this->addStoredData('tabular', compact('key', 'value'), $context);
    }

    /**
     * Adds a url with an optional description. By passing an optional context,
     * the data may be associated with this context.
     *
     * @param string      $url
     * @param string      $description
     * @param SimpleORMap $context      Optional context
     */
    public function addURL($url, $description = null, SimpleORMap $context = null)
    {
        $this->addStoredData('url', compact('url', 'description'), $context);
    }

    /**
     * Adds stored data. By passing an optional context, the data may be
     * associated with this context.
     *
     * @param string      $type    Type of data
     * @param mixed       $data
     * @param SimpleORMap $context Optional context
     */
    protected function addStoredData($type, $data, SimpleORMap $context = null)
    {
        if (!isset($this->data[$type])) {
            throw new Exception('Invalid data type');
        }

        $hash = md5(serialize($data));

        $this->data[$type][$hash] = $data;

        if ($context === null) {
            return;
        }

        $context_data = &$this->getContextData($context);

        $context_data[] =  compact('type', 'hash');
    }

    /**
     * Returns an array reference where context specific lookup data should be
     * stored.
     *
     * @param SimpleORMap $context
     * @return array reference
     */
    protected function &getContextData(SimpleORMap $context)
    {
        $context_type = get_class($context);

        if (!isset($this->contexts[$context_type])) {
            throw new Exception('Invalid context type given, only courses, institutes and users are valid contexts');
        }

        if (!isset($this->contexts[$context_type][$context->id])) {
            $this->contexts[$context_type][$context->id] = [];
        }

        return $this->contexts[$context_type][$context->id];
    }

    /**
     * Returns the stored data for a specific context.
     *
     * @param SimpleORMap $context
     * @return array
     */
    public function getStoredDataForContext(SimpleORMap $context)
    {
        $data = $this->getContextData($context);

        $result = array_fill_keys(array_keys($this->data), []);
        foreach ($data as $key => $row) {
            extract($row);

            $result[$type][] = $this->data[$type][$hash];
        }
        return $result;
    }

    /**
     * Removes the stored data for a specific context.
     *
     * @param SimpleORMap $context
     */
    public function removeStoredDataForContext(SimpleORMap $context)
    {
        $data = $this->getContextData($context);

        foreach ($data as $key => $row) {
            extract($row);

            unset($this->data[$type][$hash]);
        }

        $data = [];
    }

    /**
     * Exports the stored data to a specific location.
     *
     * @param string $location
     * @param string $name
     */
    public function export($location, $name = null)
    {
        $location = rtrim($location, '/');

        if (!is_dir($location)) {
            throw new Exception('Given location is not a folder');
        }
        if (!is_writable($location)) {
            throw new Exception('Cannot write to given location');
        }

        // Export tabular data
        if ($this->data['tabular']) {
            $name = $name ?: 'plugin-data';

            file_put_contents(
                "{$location}/{$name}.json",
                json_encode($this->data['tabular'])
            );
        }

        // Export files
        // TODO: Duplicate filenames?
        foreach ($this->data['file'] as $file) {
            $file->store($location);
        }
    }
}
