<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* html_head.inc.php
*
* output of html-head for all Stud.IP pages<br>
*
* @author       Stefan Suchi <suchi@data-quest.de>
* @access       public
* @package      studip_core
* @modulegroup  library
* @module       html_head.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// html_head.inc.php
// Copyright (c) 2002 Stefan Suchi <suchi@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <title data-original="<?= htmlReady(PageLayout::getTitle()) ?>">
        <?= htmlReady(PageLayout::getTitle() . ' - ' . Config::get()->UNI_NAME_CLEAN) ?>
    </title>
    <script>
    CKEDITOR_BASEPATH = "<?= Assets::url('javascripts/ckeditor/') ?>";
    String.locale = "<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>";
    </script>
    <? if ($_SESSION['_language'] !== 'de_DE'): ?>
        <link rel="localization" hreflang="<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>"
              href="<?= URLHelper::getScriptLink('dispatch.php/localizations/' . $_SESSION['_language']) ?>" type="application/vnd.oftn.l10n+json">
    <? endif ?>

    <script>
    window.STUDIP = {
        ABSOLUTE_URI_STUDIP: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
        ASSETS_URL: "<?= $GLOBALS['ASSETS_URL'] ?>",
        CSRF_TOKEN: {
            name: '<?=CSRFProtection::TOKEN?>',
            value: '<? try {echo CSRFProtection::token();} catch (SessionRequiredException $e){}?>'
        },
        STUDIP_SHORT_NAME: "<?= htmlReady(Config::get()->STUDIP_SHORT_NAME) ?>",
        URLHelper: {
            base_url: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
            parameters: <?= json_encode(URLHelper::getLinkParams(), JSON_FORCE_OBJECT) ?>
        },
        jsupdate_enable: <?= json_encode(
                         is_object($GLOBALS['perm']) &&
                         $GLOBALS['perm']->have_perm('autor') &&
                         PersonalNotifications::isActivated()) ?>,
        wysiwyg_enabled: <?= json_encode((bool) Config::get()->WYSIWYG) ?>
    }
    </script>

    <?= PageLayout::getHeadElements() ?>

    <script>
    window.STUDIP.editor_enabled = <?= json_encode((bool) Studip\Markup::editorEnabled()) ?> && CKEDITOR.env.isCompatible;
    </script>
</head>

<body id="<?= PageLayout::getBodyElementId() ?>">
<div id="layout_wrapper">
    <? SkipLinks::insertContainer() ?>
    <?= PageLayout::getBodyElements() ?>
