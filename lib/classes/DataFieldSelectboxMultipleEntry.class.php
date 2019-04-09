<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldSelectboxMultipleEntry extends DataFieldSelectboxEntry
{
    const SEPARATOR = '|';

    /**
     * Constructs this datafield
     *
     * @param DataField $datafield Underlying model
     * @param String    $rangeID   Range id
     * @param mixed     $value     Value
     */
    public function __construct(DataField $datafield = null, $rangeID = '', $value = null)
    {
        parent::__construct($datafield, $rangeID, $value);

        if ($this->getValue() === null) {
            $this->setValue('');
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
        return parent::getHTML($name, $variables + [
            'multiple' => true,
            'value'    => explode(self::SEPARATOR, $this->value)
        ]);
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        $value = $this->getValue();
        if ($value) {
            $type_param = $this->type_param;

            $mapper = 'trim';
            if ($this->is_assoc_param) {
                $mapper = function ($a) use ($type_param) {
                    $a = trim($a);
                    return $type_param[$a];
                };
            }

            $value = explode(self::SEPARATOR, $value);
            $value = array_map($mapper, $value);
            $value = implode('; ', $value);
        }
        return $entities
            ? htmlReady($value)
            : $value;
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($value)
    {
        if (is_array($value)) {
            $value = array_map('trim', $value);
            $value = array_filter($value);
            $value = array_unique($value);
            $value = implode(self::SEPARATOR, $value);
        } else {
            $value = '';
        }

        parent::setValueFromSubmit($value);
    }
}
