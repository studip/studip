<?php
/**
 * Adapter to fake user_data property in UserManagement
 *
 * @author noack
 * @license GPL2
 */
class UserDataAdapter implements ArrayAccess, Countable, IteratorAggregate
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $offset
     * @return string
     */
    public function adaptOffset($offset)
    {
        $adapted = trim(mb_strstr($offset, '.'), '.');
        return $adapted ?: $offset;
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return $this->user->offsetExists($this->adaptOffset($offset));
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->user->offsetGet($this->adaptOffset($offset));
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        return $this->user->offsetSet($this->adaptOffset($offset), $value);
    }

    /**
     * ArrayAccess: unset the value at the given offset.
     */
    public function offsetUnset($offset)
    {
        return $this->user->offsetUnset($this->adaptOffset($offset));
    }

    /**
     * @see Countable::count()
     */
    public function count()
    {
        return $this->user->count();
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return $this->user->getIterator();
    }

    /**
      * @param array $data
      * @param bool $reset
      */
    public function setData($data, $reset = false)
    {
        $adapted_data = [];
        foreach ($data as $k => $v) {
            $adapted_data[$this->adaptOffset($k)] = $v;
        }
        $this->user->setData($adapted_data, $reset);
    }
}
