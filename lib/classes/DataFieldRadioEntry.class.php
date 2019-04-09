<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldRadioEntry extends DataFieldSelectboxEntry
{
    protected $template = 'radio.php';

    /**
     * Returns the number of html fields this datafield uses for input.
     *
     * @return int representing the number of html fields
     */
    public function numberOfHTMLFields()
    {
        return count($this->type_param);
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
            'type_param' => $this->type_param,
            'is_assoc'   => $this->is_assoc_param
        ]);
    }
}
