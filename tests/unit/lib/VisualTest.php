<?php

/*
 * Copyright (C) 2010 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../bootstrap.php';
require_once 'config.inc.php'; //$SMILE_SHORT and $SYMBOL_SHORT needed by formatReady
require_once 'lib/models/SimpleORMap.class.php';
require_once 'lib/models/OpenGraphURL.class.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/Config.class.php';
require_once 'lib/classes/SmileyFormat.php';

class VisualFunctionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        static $config = [
            'LOAD_EXTERNAL_MEDIA' => 'allow',
            'OPENGRAPH_ENABLE'    => false,
        ];

        Config::set(new Config($config));

        $GLOBALS['SMILEY_NO_DB'] = true;
    }

    public function tearDown()
    {
        $GLOBALS['SMILEY_NO_DB'] = false;
    }

    public function testFormatReady()
    {
        $expected = '<strong>some code</strong>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady('*some*code*'));
    }

    public function testHtmlReady()
    {
        $pairs = [
          'abc'    => 'abc',
          'äöü'    => 'äöü',
          '<'      => '&lt;',
          '"'      => '&quot;',
          "'"      => '&#039;',
          '&amp;'  => '&amp;amp;',
          '&#039;' => '&amp;#039;',
          ''       => '',
          NULL     => NULL
        ];

        foreach ($pairs as $string => $expected) {
          $this->assertEquals($expected, htmlReady($string));
        }
    }

    public function testFormatReadyTicket1255()
    {
        $input = "!\nHallo Welt";
        $expected = "!<br>Hallo Welt";
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testLine()
    {
        $input = "Test\n--\nTest";
        $expected = 'Test<br><hr class="content"><br>Test';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testHeading()
    {
        $input = '!!%%Überschrift%%';
        $expected = '<h3 class="content"><em>Überschrift</em></h3>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testBoldItalics()
    {
        $input = '**some %%code%%**';
        $expected = '<strong>some <em>code</em></strong>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testBigSmall()
    {
        $input = '++some --code--++';
        $expected = '<big>some <small>code</small></big>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testSimpleBoldItalics()
    {
        $input = '*bold*text* %some%italics%';
        $expected = '<strong>bold text</strong> <em>some italics</em>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testMissingClose()
    {
        $input = '**missing %%close';
        $expected = $this->wrap($input);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testCloseBeforeOpen()
    {
        $input = 'there is -}no markup{- here';
        $expected = $this->wrap($input);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testIncorrectNesting()
    {
        $input = '** test %% test ** test %%';
        $expected = '** test <em> test ** test </em>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testImage()
    {
        $input = '[img=Stud.IP-Logo]http://www.studip.de/logo.png';
        $expected = '<img src="http://www.studip.de/logo.png" style="" title="Stud.IP-Logo" alt="Stud.IP-Logo">';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testTable()
    {
        $input = "|Name|Matrikelnummer|Studiengang|\n|Max Mustermann|55555|Mathe Diplom|\n";
        $expected = '<table class="content">'
                   .'<tr>'
                   .'<td>Name</td>'
                   .'<td>Matrikelnummer</td>'
                   .'<td>Studiengang</td>'
                   .'</tr>'
                   .'<tr>'
                   .'<td>Max Mustermann</td>'
                   .'<td>55555</td>'
                   .'<td>Mathe Diplom</td>'
                   .'</tr>'
                   .'</table>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testList()
    {
        $input = "- Einführung\n- Hauptteil\n-= Argument 1\n-= Argument 2\n- Schluss\n";
        $expected = '<ul>'
                   .'<li>Einführung</li>'
                   .'<li>Hauptteil<ol>'
                   .'<li>Argument 1</li>'
                   .'<li>Argument 2</li>'
                   .'</ol></li>'
                   .'<li>Schluss</li>'
                   .'</ul>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testIndent()
    {
        $input = "  Ebene 1\n    Ebene 2\n    Ebene 2\n  Ebene 1\n";
        $expected = '<div class="indent">'
                   .'Ebene 1<br>'
                   .'<div class="indent">'
                   .'Ebene 2<br>'
                   .'Ebene 2<br>'
                   .'</div>'
                   .'Ebene 1<br>'
                   .'</div>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input, false));
    }

    public function testNop()
    {
        $input = '[nop]**A**[quote]B[/quote]{-C-}[/nop]';
        $expected = '**A**[quote]B[/quote]{-C-}';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testPre()
    {
        $input = '[pre]**A**{-C-}[/pre]';
        $expected = '<pre><strong>A</strong><s>C</s></pre>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testQuote()
    {
        $input = '[quote=__Anonymous__]some text[/quote]';
        $expected = '<blockquote>'
                   .'<div class="author"><u>Anonymous</u> hat geschrieben:</div>some text'
                   .'</blockquote>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testLink()
    {
        $input = '[Testlink]https://www.studip.de/';
        $expected = '<a class="link-extern" href="https://www.studip.de/" target="_blank" rel="noreferrer noopener">Testlink</a>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    public function testMail()
    {
        $input = '[Mail]some.user+tag@example.com';
        $expected = '<a class="link-extern" href="mailto:some.user+tag@example.com">Mail</a>';
        $expected = $this->wrap($expected);
        $this->assertEquals($expected, formatReady($input));
    }

    private function wrap($string)
    {
        return sprintf(FORMATTED_CONTENT_WRAPPER, $string);
    }
}
