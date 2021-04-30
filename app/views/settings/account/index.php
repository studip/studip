<?php
$genders = [
        _('unbekannt'),
        _('männlich'),
        _('weiblich'),
        _('divers'),
];
?>

<? if ($user->auth_plugin !== 'standard'): ?>
    <?= MessageBox::info(sprintf(_('Einige Ihrer persönlichen Daten werden nicht in Stud.IP verwaltet '
                                   . 'und können daher hier nicht geändert werden.'))) ?>
<? endif; ?>

<? if ($locked_info): ?>
    <?= MessageBox::info(formatLinks($locked_info)) ?>
<? endif; ?>

<form id="edit_userdata" method="post" name="pers" class="default"
      action="<?= $controller->link_for('settings/account/store') ?>"
        <? if (!$restricted) echo 'data-validate="true"'; ?>>
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <fieldset>
        <legend>
            <?= _('Persönliche Angaben bearbeiten') ?>
        </legend>
        <label for="new_username">
               <span class="required">
                    <?= _('Nutzername') ?>
               </span>
            <? if ($restricted) : ?>
                <?= tooltipIcon('Dieses Feld dürfen Sie nicht ändern, Adminzugriff ist hier nicht erlaubt!') ?>
            <? endif ?>
            <input required type="text" name="new_username" id="new_username"
                   pattern="<?= htmlReady(trim($validator->username_regular_expression, '/i^$()')) ?>"
                   data-message="<?= _('Der Benutzername ist unzulässig. Er muss mindestens 4 Zeichen lang sein und darf keine Sonderzeichen oder Leerzeichen enthalten.') ?>"
                   value="<?= $user['username'] ?>"
                   autocorrect="off" autocapitalize="off"
                    <? if ($restricted || !$controller->shallChange('auth_user_md5.username')) echo 'disabled'; ?>>
        </label>
        <label class="col-3">
            <span class="required">
                <?= _('Vorname') ?>
            </span>
            <? if ($restricted) : ?>
                <?= tooltipIcon('Dieses Feld dürfen Sie nicht ändern, Adminzugriff ist hier nicht erlaubt!') ?>
            <? endif ?>
            <input required type="text" name="vorname"
                   pattern="<?= htmlReady(trim($validator->name_regular_expression, '/i^$()')) ?>"
                   value="<?= htmlReady($user['Vorname']) ?>"
                    <? if ($restricted || !$controller->shallChange('auth_user_md5.Vorname', 'name')) echo 'disabled'; ?>>
        </label>
        <label class="col-3">
            <span class="required">
                <?= _('Nachname') ?>
            </span>
            <? if ($restricted) : ?>
                <?= tooltipIcon('Dieses Feld dürfen Sie nicht ändern, Adminzugriff ist hier nicht erlaubt!') ?>
            <? endif ?>
            <input required type="text" name="nachname"
                   pattern="<?= htmlReady(trim($validator->name_regular_expression, '/i^$()')) ?>"
                   data-message="<?= _('Bitte geben Sie Ihren tatsächlichen Nachnamen an.') ?>"
                   value="<?= htmlReady($user['Nachname']) ?>"
                    <? if ($restricted || !$controller->shallChange('auth_user_md5.Nachname', 'name')) echo 'disabled'; ?>>
        </label>
        <label class="col-3">
            <?= _('Titel') ?>
            <select id="title_front_chooser" name="title_front_chooser"
                    aria-label="<?= _('Titel auswählen') ?>"
                    data-target="#title_front"
                    <? if (!$controller->shallChange('user_info.title_front', 'title')) echo 'disabled'; ?>>
                <? foreach ($GLOBALS['TITLE_FRONT_TEMPLATE'] as $title): ?>
                    <option <? if ($user['title_front'] == $title) echo 'selected'; ?>>
                        <?= htmlReady($title) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label class="col-3">
            <?= _('Titel eingeben') ?>
            <input type="text" name="title_front" id="title_front"
                   data-target="#title_front_chooser"
                   value="<?= htmlReady($user['title_front']) ?>"
                    <? if (!$controller->shallChange('user_info.title_front', 'title')) echo 'disabled'; ?>>
        </label>
        <label class="col-3">
            <?= _('Titel nachgest.') ?>
            <select name="title_rear_chooser" id="title_rear_chooser"
                    aria-label="<?= _('Titel nachgestellt auswählen') ?>"
                    data-target="#title_rear"
                    <? if (!$controller->shallChange('user_info.title_rear', 'title')) echo 'disabled'; ?>>
                <? foreach ($GLOBALS['TITLE_REAR_TEMPLATE'] as $title): ?>
                    <option <? if ($user['title_rear'] == $title) echo 'selected'; ?>>
                        <?= htmlReady($title) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label class="col-3">
            <?= _('Titel nachgest. eingeben') ?>
            <input type="text" name="title_rear" id="title_rear"
                   data-target="#title_rear_chooser"
                   value="<?= htmlReady($user['title_rear']) ?>"
                    <? if (!$controller->shallChange('user_info.title_rear', 'title')) echo 'disabled'; ?>>
        </label>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('E-Mail') ?>
            <? if ($restricted) : ?>
                <?= tooltipIcon('Dieses Feld dürfen Sie nicht ändern, Adminzugriff ist hier nicht erlaubt!') ?>
            <? endif ?>
        </legend>
        <label class="col-3">
            <span class="required"><?= _('E-Mail') ?></span>
            <input required type="email" name="email1" id="email1"
                   value="<?= htmlReady($user['Email']) ?>"
                    <? if ($restricted || !$controller->shallChange('auth_user_md5.Email')) echo 'disabled'; ?>>
        </label>
        <label class="col-3">
            <span class="required"><?= _('E-Mail Wiederholung') ?></span>
            <input required type="email" name="email2" id="email2"
                   value="<?= htmlReady($user['Email']) ?>"
                   data-must-equal="#email1"
                    <? if ($restricted || !$controller->shallChange('auth_user_md5.Email')) echo 'disabled'; ?>>
        </label>

        <? if (!$is_sso && !$restricted && $controller->shallChange('auth_user_md5.Email')): ?>
            <label class="divider email-change-confirm">

                        <span id="email-change-confirm">
                            <?= _('Falls Sie Ihre E-Mail-Adresse ändern, muss diese Änderung durch die Eingabe '
                                  . 'Ihres Passworts bestätigt werden:') ?>
                        </span>
                <input type="text" name="disable_autofill" style="display: none;">
                <input type="password" name="password" aria-labelledby="email-change-confirm">
            </label>
        <? endif; ?>

    </fieldset>
    <fieldset>
        <legend>
            <?= _('Geschlecht') ?>
        </legend>

        <? foreach ($genders as $index => $gender): ?>
            <label>
                <input type="radio" name="geschlecht" value="<?= $index ?>"
                        <? if ($user['geschlecht'] == $index) echo 'checked'; ?>
                        <? if (!$controller->shallChange('user_info.geschlecht', 'gender')) echo 'disabled'; ?>>
                <?= htmlReady($gender) ?>
            </label>
        <? endforeach; ?>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
</form>
