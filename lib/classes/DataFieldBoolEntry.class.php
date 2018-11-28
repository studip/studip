<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldBoolEntry extends DataFieldEntry
{
    protected $template = 'bool.php';

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        return $this->getValue() ? _('Ja') : _('Nein');
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue((int) $submitted_value);
    }
}
