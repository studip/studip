<?php
/**
* output of html-head for all Stud.IP pages
*
* @author  Stefan Suchi <suchi@data-quest.de>
* @license GPL2 or any later version
*/
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
    document.querySelector('html').classList.replace('no-js', 'js');
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
