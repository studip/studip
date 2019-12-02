<?php
/**
 * UserDomainTest.php - unit tests for the UserDomain class
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once 'lib/models/UserDomain.php';

class UserDomainTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        StudipTestHelper::set_up_tables(['userdomains']);
    }

    public function tearDown()
    {
        StudipTestHelper::tear_down_tables();
    }

    public function testUserVisibility()
    {
        $domains = [
            UserDomain::import(['id' => 'foo']),
        ];
        $domains_unrestricted_0 = [
            UserDomain::import(['id' => 'bar', 'restricted_access' => false]),
        ];
        $domains_unrestricted_1 = [
            UserDomain::import(['id' => 'baz', 'restricted_access' => false]),
        ];

        $this->assertTrue(UserDomain::checkUserVisibility([], []));
        $this->assertFalse(UserDomain::checkUserVisibility([], $domains));
        $this->assertFalse(UserDomain::checkUserVisibility($domains, []));
        $this->assertTrue(UserDomain::checkUserVisibility($domains, $domains));

        $this->assertTrue(UserDomain::checkUserVisibility([], $domains_unrestricted_1));
        $this->assertFalse(UserDomain::checkUserVisibility($domains, $domains_unrestricted_0));
        $this->assertFalse(UserDomain::checkUserVisibility($domains_unrestricted_0, $domains));
        $this->assertTrue(UserDomain::checkUserVisibility($domains_unrestricted_0, $domains_unrestricted_1));
    }

    public function testCourseVisibility()
    {
        $domains = [
            UserDomain::import(['id' => 'foo']),
        ];
        $domains_unrestricted = [
            UserDomain::import(['id' => 'bar', 'restricted_access' => false]),
        ];

        $this->assertTrue(UserDomain::checkCourseVisibility([], []));
        $this->assertTrue(UserDomain::checkCourseVisibility([], $domains_unrestricted));
        $this->assertFalse(UserDomain::checkCourseVisibility($domains, []));
        $this->assertTrue(UserDomain::checkCourseVisibility($domains, $domains));
        $this->assertTrue(UserDomain::checkCourseVisibility($domains, $domains_unrestricted));
        $this->assertTrue(UserDomain::checkCourseVisibility($domains_unrestricted, $domains));
    }
}
