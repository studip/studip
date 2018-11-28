<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldLinkEntry extends DataFieldEntry
{
    protected $template = 'link.php';

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return formatLinks($this->getValue());
        }
        return $this->getValue();
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        if ($submitted_value === 'http://') {
            $submitted_value = '';
        }
        $this->setValue($submitted_value);
    }

    /**
     * Returns whether the datafield contents are valid
     *
     * @return boolean indicating whether the datafield contents are valid
     */
    public function isValid()
    {
        return parent::isValid()
            && (!$this->getValue()
                || (filter_var($this->getValue(), FILTER_VALIDATE_URL)
                    && preg_match('%^(https?|ftp)://%', $this->getValue())));
    }
}
