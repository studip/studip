<?php
require_once __DIR__ . '/../../bootstrap.php';

class StringManipulationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'lib/functions.php';
    }
    
    /**
     * @dataProvider camelCaseProvider
     */
    public function testCamelCase($input, $expected, $ucfirst = false)
    {
        $camel_cased = strtocamelcase($input, $ucfirst);
        $this->assertEquals($camel_cased, $expected);
    }

    public function camelCaseProvider()
    {
        return array(
            array('foo bar', 'fooBar'),
            array('lorem (ipsum) dolor', 'loremIpsumDolor'),
            array('test with numbers 1 2 3 4', 'testWithNumbers1234'),
            array('path/definitions/converted', 'pathDefinitionsConverted'),

            array('foo bar', 'FooBar', true),
        );
    }

    /**
     * @dataProvider snake_case_provider
     */
    public function test_snake_case($input, $expected)
    {
        $snake_cased = strtosnakecase($input);
        $this->assertEquals($snake_cased, $expected);
    }

    public function snake_case_provider()
    {
        return array(
            array('foo bar', 'foo_bar'),
            array('lorem (ipsum) dolor', 'lorem_ipsum_dolor'),
            array('test with numbers 1 2 3 4', 'test_with_numbers_1_2_3_4'),
            array('path/definitions/converted', 'path_definitions_converted'),
        );
    }
}