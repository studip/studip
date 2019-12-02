<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<form method="post" action="<?= $controller->url_for('admin/user/new/' . $prelim) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Einen neuen Benutzer anlegen') ?>
        </legend>

        <label>
            <? if (!$prelim) : ?>
                <span class="required">
                    <?= _("Benutzername") ?>
                </span>
            <? else: ?>
                <?= _("Benutzername") ?>
            <? endif ?>

            <input class="user_form" type="text" name="username" value="<?= $user['username'] ?>" <?= (!$prelim ? 'required' : '')?> >
        </label>

        <label>
            <span class="required">
                <?= _("Globaler Status") ?>
            </span>

            <select class="user_form" name="perm" id="perm" onchange="jQuery('#admin_special').toggle( jQuery('#institut').val() != '0' && jQuery('#perm').val() == 'admin' )">
                <option <? if ($user['perm'] == 'user') echo 'selected'; ?>>user</option>
                <option <? if (!$user['perm'] || $user['perm'] == 'autor') echo 'selected'; ?>>autor</option>
                <option <? if ($user['perm'] == 'tutor') echo 'selected'; ?>>tutor</option>
                <option <? if ($user['perm'] == 'dozent') echo 'selected'; ?>>dozent</option>
                <? if (!$prelim) : ?>
                    <? if ($perm->is_fak_admin()) : ?>
                        <option <? if ($user['perm'] == 'admin') echo 'selected'; ?>>admin</option>
                    <? endif ?>
                    <? if ($perm->have_perm('root')) : ?>
                        <option <? if ($user['perm'] == 'root') echo 'selected'; ?>>root</option>
                    <? endif ?>
                <? endif ?>
            </select>
        </label>

        <label>
            <?= _("Sichtbarkeit") ?>
            <? if (!$prelim) : ?>
                <?= vis_chooser($user['visible'], true) ?>
            <? else : ?>
                <?= _("niemals") ?>
            <? endif ?>
        </label>

        <label>
            <span class="required">
                <?= _("Vorname") ?>
            </span>

            <input class="user_form" type="text" name="Vorname" value="<?= htmlReady($user['Vorname']) ?>" required>
        </label>

        <label>
            <span class="required">
                <?= _("Nachname") ?>
            </span>

            <input class="user_form" type="text" name="Nachname" value="<?= htmlReady($user['Nachname']) ?>" required>
        </label>

        <section>
            <?= _("Geschlecht") ?>
            <div class="hgroup">
                <label>
                    <input id="unknown" type="radio" name="geschlecht" value="0"
                           <? if (!$user['geschlecht']) echo 'checked'; ?>>
                    <?= _('unbekannt') ?>
                </label>

                <label>
                    <input id="male" type="radio" name="geschlecht" value="1"
                           <? if ($user['geschlecht'] == 1) echo 'checked'; ?>>
                    <?= _('männlich') ?>
                </label>

                <label>
                    <input id="female" type="radio" name="geschlecht" value="2"
                           <? if ($user['geschlecht'] == 2) echo 'checked'; ?>>
                    <?= _('weiblich') ?>
                </label>

                <label>
                    <input id="diverse" type="radio" name="geschlecht" value="3"
                           <? if ($user['geschlecht'] == 3) echo 'checked'; ?>>
                    <?= _('divers') ?>
            </div>
        </section>

        <label>
            <?= _("Titel") ?>

            <div class="hgroup">
                <select name="title_front_chooser" onchange="jQuery('input[name=title_front]').val( jQuery(this).val() );" class="size-s">
                <? foreach(get_config('TITLE_FRONT_TEMPLATE') as $title) : ?>
                    <option value="<?= $title ?>" <?= ($title == $user['title_front']) ? 'selected' : '' ?>><?= $title ?></option>
                <? endforeach ?>
                </select>
                <input class="user_form" type="text" name="title_front" value="<?= htmlReady($user['title_front']) ?>">
            </div>
        </label>

        <label>
            <?=_("Titel nachgestellt") ?>

            <div class="hgroup">
                <select name="title_rear_chooser" onchange="jQuery('input[name=title_rear]').val( jQuery(this).val() );" class="size-s">
                <? foreach(get_config('TITLE_REAR_TEMPLATE') as $rtitle) : ?>
                    <option value="<?= $rtitle ?>" <?= ($rtitle == $user['title_rear']) ? 'selected' : '' ?>><?= $rtitle ?></option>
                <? endforeach ?>
                </select>
                <input class="user_form" type="text" name="title_rear" value="<?= htmlReady($user['title_rear']) ?>">
            </div>
        </label>

        <label>
            <?= _('Sprache')  ?>

            <select name="preferred_language">
                <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language): ?>
                    <option value="<?= $key ?>"
                        <? if ($user['preferred_language'] == $key) echo 'selected'; ?>>
                        <?= $language['name'] ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <? if (!$prelim) : ?>
            <span class="required">
                <?= _("E-Mail") ?>
            </span>
            <? else : ?>
                <?= _("E-Mail") ?>
            <? endif ?>

            <input class="user_form" type="email" name="Email" value="<?= htmlReady($user['Email']) ?>" <?= (!$prelim ? 'required' : '')?>>
        </label>


        <? if ($GLOBALS['MAIL_VALIDATE_BOX']) : ?>
        <label>
            <input type="checkbox" id="disable_mail_host_check" name="disable_mail_host_check" value="1">
            <?= _("Mailboxüberprüfung deaktivieren") ?>
        </label>
        <? endif ?>

        <label>
            <?= _("Einrichtung") ?>

            <select id="institut" class="user_form nested-select" name="institute" onchange="jQuery('#admin_special').toggle( jQuery('#institut').val() != '0' && jQuery('#perm').val() == 'admin')">
                <option value="" class="is-placeholder">
                    <?= _('-- Bitte Einrichtung auswählen --') ?>
                </option>
                <? foreach ($faks as $fak) : ?>
                <option value="<?= $fak['Institut_id'] ?>" <?= ($user['institute'] == $fak['Institut_id']) ? 'selected' : '' ?> class="<?= $fak['is_fak'] ? 'nested-item-header' : 'nested-item'; ?>">
                    <?= htmlReady($fak['Name']) ?>
                </option>
                    <? foreach ($fak['institutes'] as $institute) : ?>
                    <option value="<?= $institute['Institut_id'] ?>" <?= ($user['institute'] == $institute['Institut_id']) ? 'selected' : '' ?> class="nested-item">
                        <?= htmlReady($institute['Name']) ?>
                    </option>
                    <? endforeach ?>
                <? endforeach ?>
            </select>
        </label>

        <div style="display: none;" id="admin_special">
            <label>
                <input type="checkbox" value="admin" name="enable_mail_admin" id="enable_mail_admin">
                <?= _('Admins der Einrichtung benachrichtigen') ?>
            </label>

            <label>
                <input type="checkbox" value="dozent" name="enable_mail_dozent" id="enable_mail_dozent">
                <?= _('Dozenten der Einrichtung benachrichtigen') ?>
            </label>
        </div>

        <? if (count($domains) > 0) : ?>
        <label>
            <?= _("Nutzerdomäne") ?>

            <select class="user_form" name="select_dom_id">
                <option value=""><?= _('-- Bitte Nutzerdomäne auswählen --') ?></option>
            <? foreach ($domains as $domain) : ?>
                <option value="<?= htmlReady($domain->id) ?>">
                    <?= htmlReady($domain->name) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
        <? endif ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Speichern'),'speichern', ['title' => _('Einen neuen Benutzer anlegen')])?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/?reset'), ['name' => 'abort'])?>
    </footer>
</form>
