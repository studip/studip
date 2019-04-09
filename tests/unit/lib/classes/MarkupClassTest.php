<?php
/**
 * markup_class_test.php - Unit tests for the Markup class.
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
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'tests/unit/fakeserver.php';

# needed by visual.inc.php
require_once 'lib/classes/DbView.class.php';
require_once 'lib/classes/TreeAbstract.class.php';

# needed by Markup.class.php
require_once 'lib/visual.inc.php';
require_once 'lib/classes/Config.class.php';

# class and functions that are tested by this script
require_once 'lib/classes/Markup.class.php';

# Seminar_Session cannot be mocked since it uses static functions.
# Also, including phplib_local.inc.php, where Seminar_Session is
# defined, introduces a massive amount of dependencies that are otherwise
# completely unneeded for testing the Markup class.
# Instead, create a fake class.
# => But note, this will fail if another test case does the same thing!
class Seminar_Session
{
    public static function is_current_session_authenticated()
    {
        return true;
    }
}

/**
 * Test case for Markup class.
 */
class MarkupTest extends PHPUnit_Framework_TestCase
{
    public function testRemoveHTML()
    {
        Config::set(new Config(['WYSIWYG' => true]));

        foreach ([
            'plain text' => 'plain text',
            '<p>paragraph only</p>' => 'paragraph only',

            '<a>no href</a>' => 'no href',
            '<a href=""></a>' => '',
            '<a href="">empty href</a>' => 'empty href',
            '<a href="href only" />' => '[ href%20only ]',
            '<a href="href end-tag"></a>' => '[ href%20end-tag ]',
            '<a href="http://href.de">and text</a>' => '[ http://href.de ]and text',
            'before <a href="http://href.de">and text</a> after'
            => 'before [ http://href.de ]and text after',

            '<img>no src</img>' => 'no src',
            '<img src="src only" />' => '[ src%20only ]',
            '<img src="src end-tag"></img>' => '[ src%20end-tag ]',
            '<img src="http://src.de">and text</a>' => '[ http://src.de ]and text',
            'before <img src="http://src.de">and text</img> after'
            => 'before [ http://src.de ]and text after',

            // some "real" urls
            '<a href="https://example.org/">Example'
            => '[ https://example.org/ ]Example',
            '<img src="https://example.org/image.png">'
            => '[ https://example.org/image.png ]',
            '<p>link <a href="http://example.org">Example-Domain</a> and picture <img src="https://example.org/image.png"></p>'
            => 'link [ http://example.org ]Example-Domain and picture [ https://example.org/image.png ]'
        ] as $in => $out) {
            $this->assertEquals($out, Studip\Markup::removeHtml(Studip\Markup::markAsHtml($in)));
        }
    }

    public function testGetMediaUrl()
    {
        # mock class Config
        $configStub = $this->getMockBuilder('Config')
        ->disableOriginalConstructor()
        ->getMock();

        $properties = [];

        $configStub->expects($this->any())
        ->method('__get')
        ->will($this->returnCallback(function ($property) use (&$properties) {
            return $properties[$property];
        }));

        $configStub->expects($this->any())
        ->method('__set')
        ->will($this->returnCallback(function ($property, $value) use (&$properties) {
            $properties[$property] = $value;
            return $properties[$property];
        }));

        Config::set($configStub);

        # exceptions
        $namespace = 'Studip\MarkupPrivate\MediaProxy\\';
        $invalidInternalLink = $namespace . 'InvalidInternalLinkException';
        $externalMediaDenied = $namespace . 'ExternalMediaDeniedException';

        # URLs
        $sendfile = 'sendfile.php?type=0&file_id=9eea7ca20cba01dd4ea394b3b53027cc&file_name=image.png';
        $wiki = 'wiki.php?cid=a07535cf2f8a72df33c12ddfa4b53dde&view=show';
        $wikipediaLogo = 'http://upload.wikimedia.org/wikipedia/meta/0/08/Wikipedia-logo-v2_1x.png';
        $proxy = 'dispatch.php/media_proxy?url=';
        $proxiedWikipediaLogo = $proxy . 'http%3A%2F%2Fupload.wikimedia.org%2Fwikipedia%2Fmeta%2F0%2F08%2FWikipedia-logo-v2_1x.png';

        # domains
        $domains = [
            'org' => 'example.org/studip',
            'home' => 'example.org/~home',
            'net' => 'example.net/studip',
        ];

        $getUrl = function ($domainKey, $path) use (&$domains) {
            return 'http://' . $domains[$domainKey] . '/' . $path;
        };

        # run various tests
        $index = 0;
        foreach ([
            [
                'in' => $getUrl('org', 'image.jpg'),
                'exception' => $invalidInternalLink,
                'uri' => $getUrl('org', 'index.php'),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('org', $sendfile),
                'uri' => $getUrl('org', 'index.php'),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('home', $sendfile),
                'uri' => $getUrl('home', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('net', $sendfile),
                'uri' => $getUrl('net', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $wikipediaLogo,
                'out' => $wikipediaLogo,
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $wikipediaLogo,
                'exception' => $externalMediaDenied,
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'deny'
            ],
            [
                'in' => $wikipediaLogo,
                'out' => $getUrl('org', $proxiedWikipediaLogo),
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'proxy'
            ],
        ] as $test) {
            $index++;

            # fake Stud.IP web server set-up
            fakeServer($test['uri'], $test['domains']);
            Config::get()->LOAD_EXTERNAL_MEDIA = $test['externalMedia'];
            //echoWebGlobals(); // call to help with debugging

            # test getMediaUrl
            try {
                $out = Studip\MarkupPrivate\MediaProxy\getMediaUrl($test['in']);

                if (isset($test['exception'])) {
                    $this->fail(
                        'Test ' . $index . ' did not raise '
                        . $test['exception'] . '. Output: ' . $out . '.'
                    );
                }
            } catch (PHPUnit_Framework_Error_Notice $e) {
                throw $e;
            } catch (Exception $e) {
                if ( !isset($test['exception'])) {
                    $this->fail(
                        'Test ' . $index . ' raised ' . get_class($e) . '.'
                    );
                }
                if (get_class($e) !== $test['exception']) {
                    $this->fail(
                        'Test ' . $index . ' raised ' . get_class($e)
                        . ' instead of ' . $test['exception'] . '.'
                    );
                }
            }
            if (isset($test['out'])) {
                $this->assertEquals($test['out'], $out, 'Test ' . $index);
            }
        }
    }
}
