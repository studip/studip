<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
session_start();

$GLOBALS['STUDIP_BASE_PATH'] = realpath(__DIR__ . '/..');

if (file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/config/config_local.inc.php')
    && !isset($_SESSION['STUDIP_INSTALLATION'])
) {
    throw new Exception(_('Diese Installation ist bereits konfiguriert'));
}

set_include_path($GLOBALS['STUDIP_BASE_PATH']);

require_once 'composer/autoload.php';
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

// Mock gettext functions if extension is not available
if (!function_exists('_')) {
    function _($what) {
        return $what;
    }
} else {
    require_once 'lib/language.inc.php';

    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) {
        list($lang, ) = explode(';', $lang);
        $lang = substr($lang, 0, 2);

        if (!in_array($lang, ['de', 'en'])) {
            continue;
        }

        setLocaleEnv($lang, 'studip');
        break;
    }
}

$GLOBALS['template_factory'] = new Flexi_TemplateFactory('../templates/');

# get plugin class from request
$dispatch_to = $_SERVER['PATH_INFO'] ?: '';

$dispatcher = new Trails_Dispatcher( '../app', $_SERVER['SCRIPT_NAME'], 'admin/install');
$dispatcher->dispatch("admin/install/{$dispatch_to}");
