<?php
/*
 * template_test.php - expression template unit tests
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require 'Template.php';

use exTpl\Template;

class TemplateTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleString()
    {
        $bindings = array();
        $template = 'The quick brown fox jumps over the layz dog.';
        $expected = $template;
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testConstantExpression()
    {
        $bindings = array();
        $template = '17 + 4 = {"foo" != "bar" ? 17 + 4 : 42.0}';
        $expected = '17 + 4 = 21';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testStringEscapes()
    {
        $bindings = array();
        $template = '"{"\\tfoo\'\\"\\n"}{\'{"bar"}\'}"';
        $expected = "\"\tfoo'\"\n{\"bar\"}\"";
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testOperatorPrecedence()
    {
        $bindings = array('val' => array(array(42)));
        $template = '{-val[0][0] / (17+4) + 8 > 6 && "foo" == "f"~"o"~"o" ? 1 : 2}';
        $expected = '2';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testSimpleBindings()
    {
        $bindings = array('foo' => 'bar', 'val' => array(17, 4), 'pi' => 3.14159);
        $template = 'foo = "{foo}", sum = {val[0] + val[1]}, pi^2 = {pi * pi}, x = {x}';
        $expected = 'foo = "bar", sum = 21, pi^2 = 9.8695877281, x = ';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testConditional()
    {
        $bindings = array('foo' => 'bar', 'pi' => 3.14159);
        $template = '{if foo == "foo"}NO{elseif foo == "bar"}pi = {pi}{else}NO{endif}';
        $expected = 'pi = 3.14159';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testConditionalIteration()
    {
        $bindings = array('foo' => 'bar', 'pi' => 3.14159);
        $template = '{foreach foo}{if foo}{foo}{endif}{endforeach}';
        $expected = 'bar';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testIteration()
    {
        $bindings = array('persons' => array(
                        1 => array('user' => 'jane', 'phone' => '555-81281'),
                        2 => array('user' => 'mike', 'phone' => '230-28382'),
                        3 => array('user' => 'john', 'phone' => '911-19212')
                    ));
        $template = '<ul>{foreach persons}<li>{index~":"~this.user~":"~phone}</li>{endforeach}</ul>';
        $expected = '<ul>' .
                    '<li>1:jane:555-81281</li>' .
                    '<li>2:mike:230-28382</li>' .
                    '<li>3:john:911-19212</li>' .
                    '</ul>';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testEmptyIteration()
    {
        $bindings = array('foo' => array(), 'bar' => false);
        $template = '{foreach foo}foo{endforeach}:{foreach bar}bar{endforeach}';
        $expected = ':';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testVariableScope()
    {
        $bindings = array('value' => 42, 'test' => array(
                        array(),
                        array('value' => 17),
                        array('test' => array(
                            array(),
                            array('value' => 4)
                        ))
                    ));
        $template = '{foreach test}{value}:{foreach test}{value}~{endforeach}{endforeach}';
        $expected = '42:42~17~42~17:17~17~17~42:42~4~';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }

    public function testNestedStatements()
    {
        foreach (range(0, 9) as $i) {
            $bindings['loop'][$i]['i'] = "$i";
        }
        $template = '{foreach loop}' .
                    '{if i+1>4 && i<(1+10/2)}{i==4*1 ? \'foo\'~i : "bar"}' .
                    '{elseif !(i<=+4)}+{elseif i==""}..{else}{"-"}{endif}' .
                    '{endforeach}';
        $expected = '----foo4bar++++';
        $tmpl_obj = new Template($template);

        $this->assertEquals($expected, $tmpl_obj->render($bindings));
    }
}
