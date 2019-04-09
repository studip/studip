<?php
/**
 * datenschutz.php
 *
 * privacy guidelines for Stud.IP
 *
 * PHP version 5
 *
 * @author      Elmar Ludwig
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @package     studip_core
 * @access      public
 */

require '../lib/bootstrap.php';

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User'
]);

// set up user session
include 'lib/seminar_open.php';

// this page must be accessible during visibility decision
$USER_VISIBILITY_CHECK = false;

PageLayout::setTitle(_('Erläuterungen zum Datenschutz'));

$template = $template_factory->open('privacy');
$template->set_layout('layouts/base.php');

echo $template->render();
