<?php
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

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
<div>
    <div class="index_container">
        <ul id="tabs" role="navigation"></ul>
        <div id="background-desktop" style="background: url(<?= $bg_desktop ?>) no-repeat top left/cover;"></div>
        <div id="background-mobile" style="background: url(<?= $bg_mobile ?>) no-repeat top left/cover;"></div>

        <div class="index_main">
            <form class="default" name="newpwd" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
                <header>
                    <h1>
                        <?= sprintf(_('Stud.IP - Neues Passwort anfordern (Schritt %s von 5)'), $step) ?>
                    </h1>
                </header>
                <? if (count($messages)): ?>
                <section>
                    <? foreach($messages as $type => $_messages) {
                        if (!empty($_messages)) {
                            foreach ($_messages as $message) {
                                echo MessageBox::$type($message);
                            }
                        }
                    }
                    ?>
                </section><br>
                <? endif ?>

                <? if ($step == 1): ?>
                    <? if (!count($messages)): ?>
                        <section>
                            <?= _('Bitte geben Sie Ihre E-Mail-Adresse an, die Sie in ' .
                                  'Stud.IP benutzen. An diese Adresse wird ihnen eine ' .
                                  'E-Mail geschickt, die einen Bestätigungslink enthält, ' .
                                  'mit dem Sie ein neues Passwort anfordern können.<br>' .
                                  'Bitte beachten Sie die Hinweise in dieser E-Mail.') ?>
                        </section>
                    <? endif ?>
                    <section>
                        <label>
                            <?= _('E-Mail') ?>
                            <input type="email" name="email" autofocus
                                   value="<?= htmlReady($email) ?>"
                                   size="20" maxlength="63">
                        </label>
                    </section>

                    <?= CSRFProtection::tokenTag() ?>
                    <input type="hidden" name="step" value="1">
                    <?= Button::createAccept(_('Abschicken'))?>
                    <?= LinkButton::createCancel(_('Abbrechen'), 'index.php?cancel_login=1')?>
                <? endif ?>
            </form>
            <? if ($step == 4 && $show_reset_password): ?>
                <section>
                    <form id="edit_password" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" class="default">
                        <?= CSRFProtection::tokenTag() ?>
                        <p><?= _('Bitte geben Sie ein neues Passwort ein.') ?></p>
                        <fieldset>
                            <label>
                                <span class="required"><?= _('Neues Passwort') ?></span>
                                <input required type="password" pattern=".{8,}"
                                       id="new_password" name="new_password"
                                       data-message="<?= _('Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.') ?>">
                            </label>
                            <label>
                                <span class="required"><?= _('Passwort bestätigen') ?></span>
                                <input required type="password" pattern=".{8,}"
                                       id="new_password_confirm" name="new_password_confirm"
                                       data-must-equal="#new_password">
                            </label>
                            <?= Button::createAccept(_('Übernehmen'), 'reset_pw') ?>
                        </fieldset>
                    </form>
                    <br>
                </section>
            <? endif ?>
            <? if ($step == 2 || $step == 4): ?>
                <section>
                    <?= $link_startpage ?>
                </section>
            <? endif ?>
        </div>
    </div>
</div>
