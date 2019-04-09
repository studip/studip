<?php

/*
 * Copyright (C) 2011 - <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/functions.php';
require_once 'lib/classes/Interactable.class.php';
require_once 'lib/classes/Button.class.php';

use \Studip\Button;

class ButtonTestCase extends PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $this->assertEquals('<button type="submit" class="button" name="ok">ok</button>', '' . Button::create());
    }

    function testCreateWithLabel()
    {
        $this->assertEquals('<button type="submit" class="button" name="yes">yes</button>', '' . Button::create('yes'));
    }

    function testCreateWithLabelAndString()
    {
        $this->assertEquals('<button type="submit" class="button" name="aName">yes</button>', '' . Button::create('yes', 'aName'));
    }

    function testCreateWithLabelAndArray()
    {
        $this->assertEquals('<button type="submit" a="1" b="2" class="button" name="yes">yes</button>',
                            '' . Button::create('yes', ['a' => 1, 'b' => 2]));
    }

    function testCreateWithLabelNameAndArray()
    {
        $this->assertEquals('<button type="submit" a="1" b="2" class="button" name="aName">yes</button>',
                            '' . Button::create('yes', 'aName', ['a' => 1, 'b' => 2]));
    }

    function testCreateAccept()
    {
        $this->assertEquals('<button type="submit" class="accept button" name="accept">Übernehmen</button>',
                            '' . Button::createAccept());
    }

    function testCreateCancel()
    {
        $this->assertEquals('<button type="submit" class="cancel button" name="cancel">Abbrechen</button>',
                            '' . Button::createCancel());
    }

    function testCreatePreOrder()
    {
        $this->assertEquals('<button type="submit" class="pre-order button" name="pre-order">ok</button>',
                            '' . Button::createPreOrder());
    }

    function testCreateWithInsaneArguments()
    {
        $this->assertEquals('<button type="submit" class="button" mad="&lt;S&gt;tu&quot;ff" name="m&amp;m">&gt;ok&lt;</button>',
                            '' . Button::create('>ok<', 'm&m', ['mad' => '<S>tu"ff']));
    }
}
