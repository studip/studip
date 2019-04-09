<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldComboEntry extends DataFieldEntry
{
    protected $template = 'combo.php';

    /**
     * Constructs this datafield
     *
     * @param DataField $datafield Underlying model
     * @param String    $rangeID   Range id
     * @param mixed     $value     Value
     */
    public function __construct(DataField $struct, $range_id, $value)
    {
        parent::__construct($struct, $range_id, $value);

        if ($this->getValue() === null) {
            $parameters = $this->getParameters();
            $this->setValue($parameters[0]); // first selectbox entry is default
        }
    }

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
        $index = $value['combo'];
        $value = $value[$index];
        parent::setValueFromSubmit($value);
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
            'values' => $this->getParameters(),
        ]);
    }

    /**
     * Returns the individual type parameters.
     *
     * @return array containing the individual type parameters
     */
    protected function getParameters()
    {
        $parameters = explode("\n", rtrim($this->model->typeparam));
        $parameters = array_map('trim', $parameters);
        return $parameters;
    }
}
