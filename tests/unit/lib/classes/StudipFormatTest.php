<?php
/*
 * studip_format_test.php - unit tests for the StudipCoreFormat class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/TextFormat.php';
require_once 'lib/classes/StudipCoreFormat.php';

function markupBold($markup, $matches, $contents)
{
    return '<b>' . $contents . '</b>';
}

class StudipFormatTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->old_rules = StudipCoreFormat::getStudipMarkups();
    }

    function tearDown()
    {
        foreach(StudipCoreFormat::getStudipMarkups() as $key => $value) {
            StudipCoreFormat::removeStudipMarkup($key);
        }

        foreach($this->old_rules as $key => $value) {
            StudipCoreFormat::addStudipMarkup($key, @$value['start'], @$value['end'], @$value['callback']);
        }
    }

    public function testAddStudipMarkup()
    {
        StudipCoreFormat::addStudipMarkup('bb-bold', '\[b\]', '\[\/b\]', 'markupBold', 'links');
        $markup = new StudipCoreFormat();

        $input = '[b]some %%code%%[/b]';
        $expected = '<b>some <em>code</em></b>';
        $this->assertEquals($expected, $markup->format($input));
    }

    public function testRemoveStudipMarkup()
    {
        StudipCoreFormat::removeStudipMarkup('bold');
        $markup = new StudipCoreFormat();

        $input = '**some %%code%%**';
        $expected = '**some <em>code</em>**';
        $this->assertEquals($expected, $markup->format($input));
    }

    public function testTextSizing()
    {
        $markup = new StudipCoreFormat();

        $input = '++++abc++++ **++123++**';
        $expected = '<big><big>abc</big></big> <strong><big>123</big></strong>';
        $this->assertEquals($expected, $markup->format($input));
    }

    public function testHtmlEnclosedMarkup()
    {
        $markup = new StudipCoreFormat();
        $index = 0;
        forEach ([
            '<p>' . PHP_EOL
            . '- single item' . PHP_EOL
            . '</p>'
            =>
            '<p>' . PHP_EOL
            . '<ul><li>single item</li></ul>'
            . '</p>',

            '<p>' . PHP_EOL
            . '- a' . PHP_EOL
            . '- list' . PHP_EOL
            . '</p>'
            => '<p>' . PHP_EOL
            . '<ul>'
            . '<li>a</li>'
            . '<li>list</li>'
            . '</ul>'
            . '</p>'
        ] as $in => $out) {
            ++$index;
            $this->assertEquals($out, $markup->format($in), 'test number ' . $index);
        }
    }

    public function testTable()
    {
        $markup = new StudipCoreFormat();
        $index = 0;
        forEach ([
            '|a|table' . PHP_EOL
            =>
            '<table class="content">'
            . '<tr><td>a</td><td>table</td></tr>'
            . '</table>',

            '| this  | is a | table |' . PHP_EOL
            . '| with | two | rows |'
            =>
            '<table class="content">'
            . '<tr><td>this</td><td>is a</td><td>table</td></tr>'
            . '<tr><td>with</td><td>two</td><td>rows</td></tr>'
            . '</table>'
        ] as $in => $out) {
            ++$index;
            $this->assertEquals($out, $markup->format($in), 'test number ' . $index);
        }
    }
}
