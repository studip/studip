<?php
# Lifter010: TODO

// Get background images (this should be resolved differently since mobile
// browsers might still download the desktop background)
$bg_desktop = LoginBackground::getRandomPicture('desktop');
if ($bg_desktop) {
    $bg_desktop = $bg_desktop->getURL();
} else {
    $bg_desktop = URLHelper::getURL('pictures/loginbackgrounds/1.jpg');
}
$bg_mobile = LoginBackground::getRandomPicture('mobile');
if ($bg_mobile) {
    $bg_mobile = $bg_mobile->getURL();
} else {
    $bg_mobile = URLHelper::getURL('pictures/loginbackgrounds/2.jpg');
}
?>
<!-- Startseite (nicht eingeloggt) -->
<ul id="tabs" role="navigation"></ul>
<? if ($logout) : ?>
    <?= MessageBox::success(_("Sie sind nun aus dem System abgemeldet."), array_filter([$GLOBALS['UNI_LOGOUT_ADD']])) ?>
<? endif; ?>

<div id="background-desktop" style="background: url(<?= $bg_desktop ?>) no-repeat top left/cover;"></div>
<div id="background-mobile" style="background: url(<?= $bg_mobile ?>) no-repeat top left/cover;"></div>
<div class="index_main">
    <nav>
        <h1><?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?></h1>
        <? foreach (Navigation::getItem('/login') as $key => $nav) : ?>
            <? if ($nav->isVisible()) : ?>
                <? list($name, $title) = explode(' - ', $nav->getTitle()) ?>
                <div class="login_link">
                    <? SkipLinks::addLink($name, $url) ?>
                    <? if (is_internal_url($url = $nav->getURL())) : ?>
                        <a href="<?= URLHelper::getLink($url) ?>">
                    <? else : ?>
                        <a href="<?= htmlReady($url) ?>" target="_blank" rel="noopener noreferrer">
                    <? endif ?>
                            <?= htmlReady($name) ?>
                            <p>
                                <?= htmlReady($title ?: $nav->getDescription()) ?>
                            </p>
                        </a>
                </div>
            <? endif ?>
        <? endforeach ?>
    </nav>
    <footer>
    <? if ($GLOBALS['UNI_LOGIN_ADD']) : ?>
        <div class="uni_login_add">
            <?= $GLOBALS['UNI_LOGIN_ADD'] ?>
        </div>
    <? endif; ?>

        <div id="languages">
        <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $temp_language_key => $temp_language): ?>
            <a href="index.php?set_language=<?= $temp_language_key ?>">
                <?= Assets::img('languages/' . $temp_language['picture'], tooltip2($temp_language['name'])) ?>
                <?= htmlReady($temp_language['name']) ?>
            </a>
        <? endforeach; ?>
        </div>

        <div class="login_info">
            <div>
                <?= _('Aktive Veranstaltungen') ?>:
                <?= number_format($num_active_courses, 0, ',', '.') ?>
            </div>

            <div>
                <?= _('Registrierte NutzerInnen') ?>:
                <?= number_format($num_registered_users, 0, ',', '.') ?>
            </div>

            <div>
                <?= _('Davon online') ?>:
                <?= number_format($num_online_users, 0, ',', '.') ?>
            </div>

            <div>
                <a href="dispatch.php/siteinfo/show">
                    <?= _('mehr') ?> &hellip;
                </a>
            </div>
        </div>
    </footer>
</div>
