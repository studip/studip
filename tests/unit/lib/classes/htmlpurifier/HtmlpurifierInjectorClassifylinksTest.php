<?php
/**
 * htmlpurifier_injector_classifylinks_test.php - Unit tests for the HTMLPurifier_Injector_ClassifyLinks class.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once 'tests/unit/fakeserver.php';

require_once 'vendor/HTMLPurifier/HTMLPurifier.standalone.php';

# needed by visual.inc.php, which is required by link classifier
require_once 'lib/classes/DbView.class.php';
require_once 'lib/classes/TreeAbstract.class.php';

require_once 'lib/classes/htmlpurifier/HTMLPurifier_Injector_ClassifyLinks.php';

/**
 * Test case for HTMLPurifier_Injector_ClassifyLinks.
 */
class HTMLPurifier_Injector_ClassifyLinksTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = new Config([
            'CONVERT_IDNA_URL' => false,
        ]);
        Config::set($config);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testClassifyLinks($uri, $domains, $in, $out)
    {
        # create purifier
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.Custom', ['ClassifyLinks']);
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new \HTMLPurifier($config);

        # run test
        fakeServer($uri, $domains);
        $this->assertEquals($out, $purifier->purify($in));
    }

    /**
     * Data provider for testClassifyLinks.
     */
    public function dataProvider()
    {
        // domains of faked Stud.IP server
        $domains = [
            'org' => 'example.org/studip',
            'home' => 'example.org/~home',
            'net' => 'example.net/studip',
        ];

        // return absolute URL for a path on one of the faked domains
        $url = function ($domainKey, $path) use (&$domains) {
            return 'http://' . $domains[$domainKey] . '/' . $path;
        };
        $a = function ($url, $className, $content) {
            $href = ' href="' . $url . '"';
            $class = empty($className) ? '' : ' class="' . $className . '"';
            return '<a' . $href . $class . '>' . $content . '</a>';
        };

        // class names for internal and external links
        $in = 'link-intern';
        $ex = 'link-extern';

        // return test data
        return [
            [
                $url('org', 'index.php'),
                $domains,
                $a('http://www.google.de', '', 'Google'),
                $a('http://www.google.de', $ex, 'Google')
            ],
            [
                $url('org', 'index.php'),
                $domains,
                $a($url('org', 'index.php'), '', 'Main Page'),
                $a($url('org', 'index.php'), $in, 'Main Page')
            ],
            [
                $url('org', 'index.php'),
                $domains,
                $a($url('home', 'index.php'), '', 'Main Page'),
                $a($url('home', 'index.php'), $in, 'Main Page')
            ],
            [
                $url('org', 'index.php'),
                $domains,
                $a($url('net', 'index.php'), '', 'Main Page'),
                $a($url('net', 'index.php'), $in, 'Main Page')
            ],
        ];
    }

    public function tearDown()
    {
        Config::set(null);
    }
}
