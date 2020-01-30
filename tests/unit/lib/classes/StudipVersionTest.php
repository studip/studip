<?php
/*
 * StudipVersionTest.php - unit tests for the StudipVersion class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StudipVersionTest extends \Codeception\Test\Unit
{
    function setUp() {
        $this->version = $GLOBALS['SOFTWARE_VERSION'];
    }

    function tearDown()
    {
        $GLOBALS['SOFTWARE_VERSION'] = $this->version;
    }

    public function testStandardVersion()
    {
        $GLOBALS['SOFTWARE_VERSION'] = '1.0';
        $this->assertEquals('1.0', StudipVersion::getStudipVersion(true));

        $GLOBALS['SOFTWARE_VERSION'] = '11.0';
        $this->assertEquals('11.0', StudipVersion::getStudipVersion(true));

        $GLOBALS['SOFTWARE_VERSION'] = '11.11';
        $this->assertEquals('11.11', StudipVersion::getStudipVersion(true));

        $GLOBALS['SOFTWARE_VERSION'] = '1.11';
        $this->assertEquals('1.11', StudipVersion::getStudipVersion(true));
    }

    public function testAlphaBetaVersion()
    {
        $GLOBALS['SOFTWARE_VERSION'] = '1.0alpha';
        $this->assertEquals('1.0', StudipVersion::getStudipVersion(true));
        $this->assertEquals('1.0alpha', StudipVersion::getStudipVersion(false));

        $GLOBALS['SOFTWARE_VERSION'] = '11.0alpha';
        $this->assertEquals('11.0', StudipVersion::getStudipVersion(true));
        $this->assertEquals('11.0alpha', StudipVersion::getStudipVersion(false));

        $GLOBALS['SOFTWARE_VERSION'] = '11.11alpha';
        $this->assertEquals('11.11', StudipVersion::getStudipVersion(true));
        $this->assertEquals('11.11alpha', StudipVersion::getStudipVersion(false));

        $GLOBALS['SOFTWARE_VERSION'] = '1.11alpha';
        $this->assertEquals('1.11', StudipVersion::getStudipVersion(true));
        $this->assertEquals('1.11alpha', StudipVersion::getStudipVersion(false));

    }

    public function testVersionComparison()
    {
        $GLOBALS['SOFTWARE_VERSION'] = '11.11';
        $this->assertTrue(StudipVersion::newerThan('1.11'));
        $this->assertTrue(StudipVersion::newerThan('1.3'));

        $this->assertTrue(StudipVersion::olderThan('12.11'));
        $this->assertTrue(StudipVersion::olderThan('12.0'));

        $this->assertTrue(StudipVersion::matches('11.11'));
        $this->assertTrue(StudipVersion::matches('11.11', false));

        $this->assertTrue(StudipVersion::range('1.11', '33.33'));
        $this->assertFalse(StudipVersion::range('22.2', '33.33'));
    }

    public function testVersionComparisonAlphaBeta()
    {
        $GLOBALS['SOFTWARE_VERSION'] = '2.11alpha';
        $this->assertTrue(StudipVersion::newerThan('1.11'));
        $this->assertTrue(StudipVersion::newerThan('1.3'));

        $this->assertTrue(StudipVersion::olderThan('3.11'));
        $this->assertTrue(StudipVersion::olderThan('3.0'));

        $this->assertTrue(StudipVersion::matches('2.11'));
        $this->assertFalse(StudipVersion::matches('2.11', false));

        $this->assertTrue(StudipVersion::range('1.11', '33.33'));
        $this->assertFalse(StudipVersion::range('3.11', '33.33'));
    }
}
