<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldSelectboxEntry extends DataFieldEntry
{
    protected $template = 'selectbox.php';

    /**
     * Constructs this datafield
     *
     * @param DataField $datafield Underlying model
     * @param String    $rangeID   Range id
     * @param mixed     $value     Value
     */
    public function __construct(DataField $struct = null, $range_id = '', $value = null)
    {
        parent::__construct($struct, $range_id, $value);

        list($values, $is_assoc) = $this->getParameters();
        $this->is_assoc_param = $is_assoc;
        $this->type_param     = $values;

        if ($this->getValue() === null) {
            reset($values);

            if ($is_assoc) {
                $this->setValue((string)key($values));
            } else {
                $this->setValue(current($values)); // first selectbox entry is default
            }
        }
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
        $variables = array_merge([
            'multiple'   => false,
            'type_param' => $this->type_param,
            'is_assoc'   => $this->is_assoc_param,
        ], $variables);

        return parent::getHTML($name, $variables);
    }

    /**
     * Returns the individual type parameters.
     *
     * @return array containing the individual type parameters
     */
    public function getParameters()
    {
        $params = explode("\n", rtrim($this->model->typeparam));
        $params = array_map('trim', $params);

        $ret = [];
        $is_assoc = false;

        foreach ($params as $i => $p) {
            if (mb_strpos($p, '=>') !== false) {
                $is_assoc = true;

                list($key, $value) = array_map('trim', explode('=>', $p, 2));
                $ret[$key] = $value;
            } else {
                $ret[$i] = $p;
            }
        }
        return [$ret, $is_assoc];
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        $value = $this->is_assoc_param
               ? $this->type_param[$this->getValue()]
               : $this->getValue();
        return $entities ? htmlReady($value) : $value;
    }
}
