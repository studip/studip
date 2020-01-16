<?php

/**
 * date_templates_text.php - Test for date-templates.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @category    Stud.IP
 */
require_once 'lib/dates.inc.php';
require_once 'lib/visual.inc.php';

class DateTemplatesTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        //First we must initialise the StudipPDO database connection:
        $this->db_handle = new \StudipPDO(
            'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
            $GLOBALS['DB_STUDIP_USER'],
            $GLOBALS['DB_STUDIP_PASSWORD']
        );

        //Then we must start a transaction before we access the database,
        //otherwise we would spam the live database with test data!
        $this->db_handle->beginTransaction();

        //Now we tell the DBManager about the connection
        //we have established to the Stud.IP database:
        \DBManager::getInstance()->setConnection('studip', $this->db_handle);

        $this->testData = [
            'regular' => [
                'turnus_data' => [
                    '0' => [
                        'metadate_id' => '0',
                        'cycle' => '0',
                        'start_hour' => '10',
                        'start_minute' => '00',
                        'end_hour' => '12',
                        'end_minute' => '00',
                        'day' => '1',
                        'desc' => 'Vorlesung',
                        'assigned_rooms' => [
                            '1' => '2'
                        ],
                        'freetext_rooms' => [
                            '<script>alert("böse");</script>' => '16'
                        ],

                        'tostring' => 'Montag: 10:00 - 12:00',
                        'tostring_short' => 'Mo. 10:00 - 12:00',
                        'first_date' => [
                            'date' => '1287388800',
                            'end_time' => '1287396000',
                            'date_typ' => '1',
                            'raum' => '<script>alert("böse");</script>'
                        ]
                    ]
                ]
            ],

            'irregular' => [
                '0' => [
                    'date_typ' => '3',
                    'start_time' => '1273647600',
                    'end_time' => '1273662000',
                    'raum' => '<script>alert("böse");</script>',
                    'typ' => '1',
                    'tostring' => 'Mi., 12.05.2010, 09:00 - 13:00'
                ]
            ]
        ];

        date_default_timezone_set(@date_default_timezone_get());
        setlocale(LC_TIME, 'C');
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();
    }

    public function testExportTemplates()
    {
        $data = $this->renderTemplate('dates/seminar_export', $this->testData, ['show_room' => true]);
        $compare =
            'Mo. 10:00 - 12:00 (wöchentlich) - Vorlesung, Ort: Hörsaal 1 <br>, (<script>alert("böse");</script>), ' .
            "\n" .
            'Termine am 12.05. 09:00 - 13:00, Ort: (<script>alert("böse");</script>)';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/seminar_export_location', $this->testData);
        $compare =
            'Hörsaal 1 <br>: Mo. 10:00 - 12:00 (2x), ' .
            "\n" .
            '(<script>alert("böse");</script>): Mo. 10:00 - 12:00 (16x)' .
            "\n" .
            ' 12.05. 09:00 - 13:00';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/date_export', $this->testData, ['date' => new SingleDate()]);
        $compare = 'Mo., 11.11.2010 12:00 - 14:00, Ort: Hörsaal 1 <br>';
        $this->assertEquals($compare, $data);

        // test single date with freetext
        $singledate = new SingleDate();
        $singledate->resource_id = null;
        $data = $this->renderTemplate('dates/date_export', $this->testData, ['date' => $singledate]);
        $compare = 'Mo., 11.11.2010 12:00 - 14:00, Ort: (<script>alert("böse");</script>)';
        $this->assertEquals($compare, $data);
    }

    public function testHTMLTemplatesWithLink()
    {
        $data = $this->renderTemplate('dates/seminar_html', $this->testData, ['show_room' => true]);
        $compare =
            'Montag: 10:00 - 12:00 (ab 10/18/10), <i>Vorlesung</i>, Ort: ' .
            '<a onclick="window.open(...)">Hörsaal 1</a>, ' .
            '(&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)<br>' .
            'Termine am 12.05. 09:00 - 13:00, Ort: (&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/seminar_html_location', $this->testData);

        $compare = '<table class="default">
                <tr>
            <td style="vertical-align: top"><a onclick="window.open(...)">Hörsaal 1</a></td>
            <td>

                                    Montag: 10:00 - 12:00 (ab 10/18/10), <i>Vorlesung</i> (2x)                            </td>
                    <tr>
            <td style="vertical-align: top">(&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)</td>
            <td>

                                    Montag: 10:00 - 12:00 (16x)<br> 12.05. 09:00 - 13:00                            </td>
                </table>';

        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/seminar_predominant_html', $this->testData, ['cycle_id' => '0']);
        $compare = '<a onclick="window.open(...)">Hörsaal 1</a>';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/date_html', $this->testData, ['date' => new SingleDate()]);
        $compare = 'Mo., 11.11.2010 12:00 - 14:00, Ort: <a onclick="window.open(...)">Hörsaal 1</a>';
        $this->assertEquals($compare, $data);
    }

    public function testHTMLTemplatesWithoutLink()
    {
        $data = $this->renderTemplate('dates/seminar_html', $this->testData, ['link' => false, 'show_room' => true]);
        $compare =
            'Montag: 10:00 - 12:00 (ab 10/18/10), <i>Vorlesung</i>, Ort: Hörsaal 1 &lt;br&gt;, ' .
            '(&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)<br>' .
            'Termine am 12.05. 09:00 - 13:00, Ort: (&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/seminar_html_location', $this->testData, ['link' => false]);
        $compare = '<table class="default">
                <tr>
            <td style="vertical-align: top">Hörsaal 1 &lt;br&gt;</td>
            <td>

                                    Montag: 10:00 - 12:00 (ab 10/18/10), <i>Vorlesung</i> (2x)                            </td>
                    <tr>
            <td style="vertical-align: top">(&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)</td>
            <td>

                                    Montag: 10:00 - 12:00 (16x)<br> 12.05. 09:00 - 13:00                            </td>
                </table>';

        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/seminar_predominant_html', $this->testData, [
            'cycle_id' => '0',
            'link' => false
        ]);
        $compare = 'Hörsaal 1 &lt;br&gt;';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/date_html', $this->testData, ['date' => new SingleDate(), 'link' => false]);
        $compare = 'Mo., 11.11.2010 12:00 - 14:00, Ort: Hörsaal 1 &lt;br&gt;';
        $this->assertEquals($compare, $data);

        // test single date with freetext
        $singledate = new SingleDate();
        $singledate->resource_id = null;
        $data = $this->renderTemplate('dates/date_html', $this->testData, ['date' => $singledate]);
        $compare = 'Mo., 11.11.2010 12:00 - 14:00, Ort: (&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;)';
        $this->assertEquals($compare, $data);
    }

    public function testXMLTemplates()
    {
        $data = $this->renderTemplate('dates/seminar_xml', $this->testData);
        $compare = '<raumzeit>
    <startwoche>0</startwoche>
    <datum>wöchentlich</datum>
    <wochentag>Montag</wochentag>
    <zeit>10:00-12:00</zeit>
    <raum>
        <gebucht>Hörsaal 1 &lt;br&gt;</gebucht>
        <freitext>&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;</freitext>
    </raum>
</raumzeit>
<raumzeit>
    <datum>12.05.2010</datum>
    <wochentag>Mittwoch</wochentag>
    <zeit>09:00-13:00</zeit>
    <raum>
        <gebucht></gebucht>
        <freitext>&lt;script&gt;alert(&quot;böse&quot;);&lt;/script&gt;</freitext>
    </raum>
</raumzeit>';
        $this->assertEquals($compare, $data);

        $data = $this->renderTemplate('dates/date_xml', $this->testData, ['date' => new SingleDate()]);
        $compare = '<date>Mo., 11.11.2010 12:00 - 14:00, Ort: Hörsaal 1 &lt;br&gt;</date>';
        $this->assertEquals($compare, $data);
    }

    private function renderTemplate($template, $data, $params = [])
    {
        $GLOBALS['template_factory'] = new Flexi_TemplateFactory(dirname(__FILE__) . '/../../../../templates');

        $template = $GLOBALS['template_factory']->open($template);
        $template->set_attribute('dates', $data);

        $template->set_attributes($params);

        return trim($template->render());
    }
}
