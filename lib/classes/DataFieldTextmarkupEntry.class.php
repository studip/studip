<?php

/**
 * @author  Elmar Ludwig <elmar.ludwig@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldTextmarkupEntry extends DataFieldTextareaEntry
{
    protected $template = 'textmarkup.php';

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue(Studip\Markup::purifyHtml($submitted_value));
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return formatReady($this->getValue());
        }

        return $this->getValue();
    }
}
