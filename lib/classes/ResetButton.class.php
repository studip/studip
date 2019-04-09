<?php
/*
 * Copyright (c) 2012 tleilax+studip@gmail.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


namespace Studip;

/**
 * Represents an HTML reset button element. Class Button inherits from
 * Interactable and customizes the HTML output.
 */
class ResetButton extends Interactable
{

    /**
     * Initialize a Button.
     * The second parameter is used as @name attribute of the
     * resulting <button> HTML element.
     *
     * @param string $label       the label of the button element
     * @param string $name        the @name element of the button element
     * @param array  $attributes  the attributes of the button element
     */
    protected function initialize($label, $name, $attributes)
    {
//        $this->attributes['name'] = $name ?: $this->label;
    }

    /**
     * @return  returns a HTML representation of this button.
     */
    function __toString()
    {
        // add "button" to attribute @class
        @$this->attributes["class"] .= " button";

        $attributes = [];
        ksort($this->attributes);
        foreach ($this->attributes as $k => $v) {
            $attributes[] = sprintf(' %s="%s"', $k, htmlReady($v));
        }

        return sprintf('<button type="reset"%s>%s</button>',
                       join('', $attributes),
                       htmlReady($this->label));
    }
}
