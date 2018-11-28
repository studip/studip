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
     * Returns the number of html fields this datafield uses for input.
     *
     * @return int representing the number of html fields
     */
    public function numberOfHTMLFields()
    {
        return 2;
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($value)
    {
        if (is_array($value) && count($value) === 2) {
            $value = implode(':', $value);
            parent::setValueFromSubmit($value);
        }
    }

    /**
     * Returns the according input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = array())
    {
        return parent::getHTML($name, $variables + [
            'values' => explode(':', $this->value)
        ]);
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
