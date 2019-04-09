<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldDateEntry extends DataFieldEntry
{
    protected $template = 'date.php';

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($value)
    {
        if ($value) {
            $value = trim($value);
            $items = explode(".", $value);
            $value = array_reverse($items);
            $value = array_filter($value);
            $date  = implode('-', $value);
            parent::setValueFromSubmit($date);
        }
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entries = true)
    {
        if ($this->isValid()) {
            $value = trim($this->value);
            $value = explode('-', $value);
            $value = array_reverse($value);
            $value = implode('.', $value);
            return $value;
        }

        return '';
    }

    /**
     * Returns the according input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = [])
    {
        return parent::getHTML($name, $variables + [
            'timestamp' => strtotime(trim($this->value)),
        ]);
    }

    /**
     * Returns whether the datafield contents are valid
     *
     * @return boolean indicating whether the datafield contents are valid
     */
    public function isValid()
    {
        $value = trim($this->value);

        if (!$value) {
            return parent::isValid();
        }

        return parent::isValid() && strtotime($value) !== false;
    }
}
