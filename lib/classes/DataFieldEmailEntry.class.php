<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldEmailEntry extends DataFieldEntry
{
    protected $template = 'email.php';

    /**
     * Returns whether the datafield contents are valid
     *
     * @return boolean indicating whether the datafield contents are valid
     */
    public function isValid()
    {
        return parent::isValid()
            && (!$this->getValue() || filter_var($this->getValue(), FILTER_VALIDATE_EMAIL));
    }
}
