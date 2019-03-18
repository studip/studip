<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldTimeEntry extends DataFieldEntry
{
    protected $template = 'time.php';

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($value)
    {
        if ($value) {
            parent::setValueFromSubmit($value);
        }
    }

    /**
     * Checks if the datafield is empty (was not set)
     *
     * @return bool true if empty, else false
     */
    public function isEmpty()
    {
        return $this->getValue() == ':';
    }

    /**
     * Returns whether the datafield contents are valid
     *
     * @return boolean indicating whether the datafield contents are valid
     */
    public function isValid()
    {
        $parts = explode(':', $this->value);

        return parent::isValid()
            && $parts[0] >= 0 && $parts[0] <= 24
            && $parts[1] >= 0 && $parts[1] <= 59;
    }
}
