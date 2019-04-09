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
require_once 'lib/classes/URLHelper.php';
require_once 'lib/classes/Interactable.class.php';
require_once 'lib/classes/LinkButton.class.php';

use \Studip\LinkButton;

class LinkButtonTestCase extends PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $this->assertNotNull(LinkButton::create());
    }

    function testCreateWithLabel()
    {
        $this->assertEquals('<a class="button" href="?" tabindex="0">yes</a>',
                            '' . LinkButton::create('yes'));
    }

    function testCreateWithLabelAndUrl()
    {
        $this->assertEquals('<a class="button" href="http://example.net" tabindex="0">yes</a>',
                            '' . LinkButton::create('yes', 'http://example.net'));
    }

    function testCreateWithLabelAndArray()
    {
        $this->assertEquals('<a a="1" b="2" class="button" href="?" tabindex="0">yes</a>',
                            '' . LinkButton::create('yes', ['a' => 1, 'b' => 2]));
    }

    function testCreateWithLabelUrlAndArray()
    {
        $this->assertEquals('<a a="1" b="2" class="button" href="http://example.net" tabindex="0">yes</a>',
                            '' . LinkButton::create('yes', 'http://example.net', ['a' => 1, 'b' => 2]));
    }

    function testCreateAccept()
    {
        $this->assertEquals('<a class="accept button" href="?" name="accept" tabindex="0">Übernehmen</a>',
                            '' . LinkButton::createAccept());
    }

    function testCreateCancel()
    {
        $this->assertEquals('<a class="cancel button" href="?" name="cancel" tabindex="0">Abbrechen</a>',
                            '' . LinkButton::createCancel());
    }

    function testCreatePreOrder()
    {
        $this->assertEquals('<a class="pre-order button" href="?" name="pre-order" tabindex="0">ok</a>',
                            '' . LinkButton::createPreOrder());
    }

    function testCreateWithInsaneArguments()
    {
        $this->assertEquals('<a class="button" href="http://example.net?m=&amp;m=" mad="&lt;S&gt;tu&quot;ff" tabindex="0">&gt;ok&lt;</a>',
                            '' . LinkButton::create('>ok<', 'http://example.net?m=&m=', ['mad' => '<S>tu"ff']));
    }
}
