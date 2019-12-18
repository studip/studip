<?php

namespace JsonApi\Models;

class StudipProperty
{
    public $field;
    public $description;
    public $value;

    public function __construct($field, $description, $value)
    {
        $this->field = $field;
        $this->description = $description;
        $this->value = $value;
    }
}
