<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldTextareaEntry extends DataFieldEntry
{
    protected $template = 'textarea.php';

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return htmlReady($this->getValue(), true, true);
        }

        return $this->getValue();
    }
}
