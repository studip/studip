<?php
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

// Get background images (this should be resolved differently since mobile
// browsers might still download the desktop background)
if (!match_route('web_migrate.php')) {
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
} else {
    $bg_desktop = URLHelper::getURL('pictures/loginbackgrounds/1.jpg');
    $bg_mobile = URLHelper::getURL('pictures/loginbackgrounds/2.jpg');
}
?>
<div>
    <div class="index_container">
        <ul id="tabs" role="navigation"></ul>
        <div id="background-desktop" style="background: url(<?= $bg_desktop ?>) no-repeat top left/cover;"></div>
        <div id="background-mobile" style="background: url(<?= $bg_mobile ?>) no-repeat top left/cover;"></div>
        <? if ($loginerror): ?>
            <!-- failed login code -->
            <?= MessageBox::error(_('Bei der Anmeldung trat ein Fehler auf!'), [
                $error_msg,
                sprintf(
                    _('Bitte wenden Sie sich bei Problemen an: <a href="mailto:%1$s">%1$s</a>'),
                    $GLOBALS['UNI_CONTACT']
                )
            ]) ?>
        <? endif; ?>
        <div class="index_main">
            <form class="default" name="login" method="post" action="<?= URLHelper::getLink(Request::url(), ['cancel_login' => NULL]) ?>">
                <header>
                    <h1 style="margin: 0; padding-bottom:10px;">
                        <?=_('Herzlich willkommen!')?>
                    </h1>
                </header>
                <section>
                    <label>
                        <?= _('Benutzername:') ?>
                        <input type="text" <?= mb_strlen($uname) ? '' : 'autofocus' ?>
                               id="loginname" name="loginname"
                               value="<?= htmlReady($uname) ?>"
                               size="20"
                               autocorrect="off" autocapitalize="off">
                    </label>
                </section>
                <section>
                    <label for="password">
                        <?= _('Passwort:') ?>
                        <input type="password" <?= mb_strlen($uname) ? 'autofocus' : '' ?>
                               id="password" name="password" size="20">
                    </label>
                </section>
                    <?= CSRFProtection::tokenTag() ?>
                    <input type="hidden" name="login_ticket" value="<?=Seminar_Session::get_ticket();?>">
                    <input type="hidden" name="resolution"  value="">
                    <input type="hidden" name="device_pixel_ratio" value="1">
                    <?= Button::createAccept(_('Anmelden'), _('Login')); ?>
                    <?= LinkButton::create(_('Abbrechen'), URLHelper::getURL('index.php', ['cancel_login' => 1], true)) ?>
            </form>

            <div>
                <? if (Config::get()->ENABLE_REQUEST_NEW_PASSWORD_BY_USER && in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])): ?>
                    <a href="<?= URLHelper::getLink('request_new_password.php?cancel_login=1') ?>">
                <? else: ?>
                    <a href="mailto:<?= $GLOBALS['UNI_CONTACT'] ?>?subject=<?= rawurlencode('Stud.IP Passwort vergessen - '.Config::get()->UNI_NAME_CLEAN) ?>&amp;body=<?= rawurlencode("Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername: " . htmlReady($uname) . "\n") ?>">
                <? endif; ?>
                        <?= _('Passwort vergessen') ?>
                    </a>
                <? if ($self_registration_activated): ?>
                    /
                    <a href="<?= URLHelper::getLink('register1.php?cancel_login=1') ?>">
                        <?= _('Registrieren') ?>
                    </a>
                <? endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" language="javascript">
//<![CDATA[
$(function () {
    $('form[name=login]').submit(function () {
        $('input[name=resolution]', this).val( screen.width + 'x' + screen.height );
        $('input[name=device_pixel_ratio]').val(window.devicePixelRatio || 1);
    });
});
// -->
</script>
