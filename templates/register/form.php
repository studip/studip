<?php
use Studip\Button, Studip\LinkButton;

$email_restriction = Config::get()->EMAIL_DOMAIN_RESTRICTION;
?>
<script type="text/javascript" language="javaScript">
jQuery(document).ready(function() {
    STUDIP.register.re_username = <?= $validator->username_regular_expression ?>;
    STUDIP.register.re_name = <?= $validator->name_regular_expression ?>;

    $('form[name=login]').submit(function () {
        return STUDIP.register.checkdata();
    });
});
</script>

<? if (isset($username)): ?>
    <?= MessageBox::error(_("Bei der Registrierung ist ein Fehler aufgetreten!"), [$error_msg, _("Bitte korrigieren Sie Ihre Eingaben und versuchen Sie es erneut")]) ?>
<? endif; ?>

<h1><?= _('Stud.IP - Registrierung') ?></h1>

<form name="login" action="<?= URLHelper::getLink() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="login_ticket" value="<?= Seminar_Session::get_ticket() ?>">

    <fieldset>
        <legend><?= _('Herzlich willkommen!') ?></legend>

        <p><?= _('Bitte füllen Sie zur Anmeldung das Formular aus:') ?></p>

        <label for="username">
            <em class="required"><?= _('Benutzername') ?></em>
            <input type="text" name="username" id="username"
                   onchange="STUDIP.register.checkusername()"
                   value="<?= htmlReady($username) ?>"
                   autofocus
                   required maxlength="63"
                   autocapitalize="off" autocorrect="off">
        </label>

        <label for="password">
            <em class="required"><?= _('Passwort') ?></em>
            <input type="password" name="password" id="password"
                   onchange="STUDIP.register.checkpassword()"
                   required maxlength="31">
        </label>

        <label for="password2">
            <em class="required"><?= _('Passwortbestätigung') ?></em>
            <input type="password" name="password2" id="password2"
                   onchange="STUDIP.register.checkpassword2()"
                   required maxlength="31">
        </label>

        <label for="title_front">
            <?= _('Titel') ?>
        </label>
        <section class="hgroup size-m">
            <select name="title_chooser_front" data-copy-to="#title_front" class="size-s">
            <? foreach ($GLOBALS['TITLE_FRONT_TEMPLATE'] as $template): ?>
                <option <? if ($template === $title_front) echo 'selected'; ?>>
                    <?= htmlReady($template) ?>
                </option>
            <? endforeach; ?>
            </select>

            <input type="text" name="title_front" id="title_front"
                   value="<?= htmlReady($title_front) ?>"
                   maxlength="63" class="no-hint">
        </section>

        <label for="title_rear">
            <?= _('Titel nachgestellt') ?>
        </label>
        <section class="hgroup size-m">
            <select name="title_chooser_rear" data-copy-to="#title_rear" class="size-s">
            <? foreach ($GLOBALS['TITLE_REAR_TEMPLATE'] as $template): ?>
                <option <? if ($template === $title_rear) echo 'selected'; ?>>
                    <?= htmlReady($template) ?>
                </option>
            <? endforeach; ?>
            </select>

            <input type="text" name="title_rear" id="title_rear"
                   value="<?= htmlReady($title_rear) ?>"
                   maxlength="63" class="no-hint">
        </section>

        <label for="first_name">
            <em class="required"><?= _('Vorname') ?></em>

            <input type="text" name="Vorname" id="first_name"
                   onchange="STUDIP.register.checkVorname()"
                   value="<?= htmlReady($Vorname) ?>"
                   required maxlength="63">
        </label>

        <label for="last_name">
            <em class="required"><?= _('Nachname') ?></em>

            <input type="text" name="Nachname" id="last_name"
                   onchange="STUDIP.register.checkNachname()"
                   value="<?= htmlReady($Nachname) ?>"
                   required maxlength="63">
        </label>

        <div>
            <?= _('Geschlecht') ?>
        </div>

        <section class="hgroup" id="gender">
            <label>
                <input type="radio" name="geschlecht" value="0"
                       <? if (!$geschlecht) echo 'checked' ?>>
                <?= _('unbekannt') ?>
            </label>

            <label>
                <input type="radio" name="geschlecht" value="1"
                       <? if ($geschlecht == 1) echo "checked" ?>>
                <?= _('männlich') ?>
            </label>

            <label>
                <input type="radio" name="geschlecht" value="2"
                       <? if ($geschlecht == 2) echo "checked" ?>>
                <?= _('weiblich') ?>
            </label>

            <label>
                <input type="radio" name="geschlecht" value="3"
                       <? if ($geschlecht == 3) echo "checked" ?>>
                <?= _('divers') ?>
            </label>
        </section>


        <label for="email">
            <em class="required"><?= _('E-Mail') ?></em>
        <? if (!trim($email_restriction)): ?>
            <input type="email" name="Email" id="email"
                   onchange="STUDIP.register.checkEmail()"
                   value="<?= htmlReady(trim($Email)) ?>"
                   required maxlength="63">
        <? endif; ?>
        </label>

    <? if (trim($email_restriction)): ?>
        <section class="hgroup size-m">
            <input type="text" name="Email" id="email"
                   onchange="STUDIP.register.checkEmail()"
                   value="<?= htmlReady(preg_replace('/@.*$/', '', trim($Email ?: ''))) ?>"
                   required maxlength="63"
                   class="no-hint">
            <select name="emaildomain">
            <? foreach (explode(',', $email_restriction) as $domain): ?>
                <option value="<?= trim($domain) ?>"
                        <? if (trim($domain) == Request::get('emaildomain')) echo 'selected'; ?>>
                    @<?= trim($domain) ?>
                </option>
            <? endforeach; ?>
            </select>
        </section>
    <? endif; ?>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Registrieren'))?>
        <?= LinkButton::createCancel(
            _('Registrierung abbrechen'),
            URLHelper::getURL('index.php?cancel_login=1')
        ) ?>
    </footer>
</form>
