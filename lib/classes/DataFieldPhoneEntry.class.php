<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldPhoneEntry extends DataFieldEntry
{
    protected $template = 'phone.php';

    /**
     * Returns the number of html fields this datafield uses for input.
     *
     * @return int representing the number of html fields
     */
    public function numberOfHTMLFields()
    {
        return 3;
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($value)
    {
        if (is_array($value)) {
            $value = array_slice($value, 0, 3);
            $value = implode("\n", $value);
            $value = str_replace(' ', '', $value);

            parent::setValueFromSubmit($value);
        }
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        list($country, $area, $phone) = $this->getNumberParts();

        if ($country || $area || $phone) {
            if ($country) {
                $country = "+$country";
            }

            return "$country $area $phone";
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
            'values' => $this->getNumberParts()
        ]);
    }

    /**
     * Checks if the datafield is empty (was not set)
     *
     * @return bool true if empty, else false
     */
    public function isEmpty()
    {
        return $this->getValue() == "\n\n";
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

        return parent::isValid()
            && preg_match('/^([1-9]\d*)?\n[1-9]\d+\n[1-9]\d+(-\d+)?$/', $value);
    }

    /**
     * Retturns the individual parts of the telephone number.
     * The resulting array is always padded to contain at least
     * three items.
     *
     * @return array containing the individual parts.
     */
    protected function getNumberParts()
    {
        $values = explode("\n", $this->value);

        // pad values array to a size of 3 by inserting empty values from left
        while (count($values) < 3) {
            array_unshift($values, '');
        }

        return $values;
    }
}

