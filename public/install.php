<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
session_start();

$GLOBALS['STUDIP_BASE_PATH'] = realpath(dirname(__FILE__) . '/..');

if (file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/config/config_local.inc.php')
    && !isset($_SESSION['STUDIP_INSTALLATION'])
) {
    throw new Exception('Diese Installation ist bereits konfiguriert');
}

set_include_path($GLOBALS['STUDIP_BASE_PATH']);

require_once 'lib/visual.inc.php';
require_once 'vendor/trails/trails.php';
require_once 'vendor/flexi/flexi.php';
require_once 'lib/classes/URLHelper.php';
require_once 'lib/classes/LayoutMessage.interface.php';
require_once 'lib/classes/MessageBox.class.php';
require_once 'lib/classes/Request.class.php';
require_once 'lib/classes/Interactable.class.php';
require_once 'lib/classes/Button.class.php';
require_once 'lib/classes/LinkButton.class.php';
require_once 'lib/classes/StudipInstaller.php';
require_once 'lib/classes/SystemChecker.php';
require_once 'lib/classes/Markup.class.php';
require_once 'vendor/phpass/PasswordHash.php';

$GLOBALS['template_factory'] = new Flexi_TemplateFactory('../templates/');

# get plugin class from request
$dispatch_to = $_SERVER['PATH_INFO'] ?: '';

$dispatcher = new Trails_Dispatcher( '../app', $_SERVER['SCRIPT_NAME'], 'admin/install');
$dispatcher->dispatch($dispatch_to);
