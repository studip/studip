<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if ($flash['delete']) : ?>
    <?= $this->render_partial('admin/user/_delete', ['data' => $flash['delete']]) ?>
<? endif ?>

<span class="content-title">
    <?= sprintf(_('Benutzerverwaltung für %s'), htmlReady($user->getFullName())) ?>

    <? if ($prelim): ?>
        (<?= _('vorläufiger Benutzer') ?>)
    <? endif; ?>
    <? if ($user->locked): ?>
        <br>
        <span style="color: red">
            (<?= sprintf(_('gesperrt von %s'), htmlReady(get_fullname($user->locked_by))) ?>
        <? if ($user->lock_comment): ?>
            , <?= _('Kommentar') ?>: <?= htmlReady($user->lock_comment) ?>
        <? endif; ?>
            )
        </span>
    <? endif; ?>
</span>

<form method="post" action="<?= $controller->url_for('admin/user/edit/' . $user->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Allgemeine Daten') ?>
        </legend>

        <label>
            <span class="required">
                <?= _('Benutzername') ?>
            </span>

            <input class="user_form" type="text" id="username"
                   value="<?= htmlReady($user->username) ?>" required
                   <?= StudipAuthAbstract::CheckField('auth_user_md5.username', $user->auth_plugin)
                    || LockRules::check($user->user_id, 'username') ? 'readonly' : 'name="username"' ?>>
        </label>

        <label>
            <?= _('Globaler Status') ?>

            <select name="perms[]" id="permission"
                <?= StudipAuthAbstract::CheckField('auth_user_md5.perms', $user->auth_plugin) ? 'disabled' : '' ?>>
                <? foreach (array_keys($GLOBALS['perm']->permissions) as $permission): ?>
                    <option <? if ($permission === $user->perms) echo 'selected'; ?>>
                        <?= htmlReady($permission) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label >
            <?= _('Sichtbarkeit') ?>

            <div class="hgroup">
                <? if (!$prelim): ?>
                    <?= vis_chooser($user->visible, false, 'visible') ?>
                <? endif; ?>
            </div>
        </label>

        <label >
            <span class="required">
                <?= _('Vorname') ?>
            </span>

            <input class="user_form" type="text" id="vorname"
                   value="<?= htmlReady($user->vorname) ?>" required
                   <?= StudipAuthAbstract::CheckField('auth_user_md5.Vorname', $user->auth_plugin)
                    || LockRules::check($user->user_id, 'name') ? 'readonly' : 'name="Vorname"' ?>>
        </label>

        <label >
            <span class="required">
                <?= _('Nachname') ?>
            </span>

            <input class="user_form" type="text" id="nachname"
                   value="<?= htmlReady($user->nachname) ?>" required
                   <?= StudipAuthAbstract::CheckField('auth_user_md5.Nachname', $user->auth_plugin)
                    || LockRules::check($user->user_id, 'name') ? 'readonly' : 'name="Nachname"' ?>>
        </label>

        <label >
            <?= _('Titel') ?>

            <? $disable_field = false ?>
            <? if (StudipAuthAbstract::CheckField('user_info.title_front', $user->auth_plugin) || LockRules::check($user->user_id, 'title')): ?>
                <? $disable_field = true ?>
            <? endif ?>

            <div class="hgroup">
                <select name="title_front_chooser" id="title_front" class="size-s"
                        onchange="jQuery(this).next().val(this.value);"
                        <?= $disable_field ? 'disabled' : '' ?>>
                <? foreach (Config::get()->TITLE_FRONT_TEMPLATE as $title): ?>
                    <option value="<?= htmlReady($title) ?>" <? if ($title === $user->title_front) echo 'selected'; ?>>
                        <?= htmlReady($title) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <input class="user_form" type="text"
                       value="<?= htmlReady($user->title_front) ?>"
                       <?= $disable_field ? 'readonly' : 'name="title_front"' ?>>
           </div>
        </label>


        <label >
            <?= _('Titel nachgestellt') ?>

            <? $disable_field = false ?>
            <? if (StudipAuthAbstract::CheckField('user_info.title_rear', $user->auth_plugin) || LockRules::check($user->user_id, 'title')): ?>
                <? $disable_field = true ?>
            <? endif ?>

            <div class="hgroup">
                <select name="title_rear_chooser" id="title_rear" class="size-s"
                        onchange="jQuery(this).next().val(this.value);"
                         <?= $disable_field ? 'disabled' : '' ?>>
                <? foreach (Config::get()->TITLE_REAR_TEMPLATE as $rtitle): ?>
                    <option value="<?= htmlReady($rtitle) ?>" <? if ($rtitle === $user->title_rear) echo 'selected'; ?>>
                        <?= htmlReady($rtitle) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <input class="user_form" type="text"
                       value="<?= htmlReady($user->title_rear) ?>"
                        <?= $disable_field ? 'readonly' : 'name="title_rear"' ?>>
           </div>
        </label>

        <label >
            <?= _('Sprache')  ?>

            <select name="preferred_language">
                <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language): ?>
                    <option value="<?= $key ?>"
                        <? if ($user->preferred_language == $key) echo 'selected'; ?>>
                        <?= $language['name'] ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <section class="col-2">
            <span class="label-text">
                <?= _('Geschlecht') ?>
            </span>

            <? $disable_field = false ?>
            <? if (StudipAuthAbstract::CheckField('user_info.geschlecht', $user->auth_plugin) || LockRules::check($user->user_id, 'gender')): ?>
                <? $disable_field = true ?>
            <? endif ?>

            <div class="hgroup">
                <label>
                    <input type="radio" name="geschlecht" value="0"
                            <? if (!$user->geschlecht) echo 'checked'; ?>
                             <?= $disable_field ? 'disabled' : '' ?>>
                    <?= _('unbekannt') ?>
                </label>
                <label>
                    <input type="radio" name="geschlecht" value="1"
                            <? if ($user->geschlecht == 1) echo 'checked'; ?>
                             <?= $disable_field ? 'disabled' : '' ?>>
                    <?= _('männlich') ?>
                </label>
                <label>
                    <input type="radio" name="geschlecht" value="2"
                            <? if ($user->geschlecht == 2) echo 'checked'; ?>
                            <?= $disable_field ? 'disabled' : '' ?>>
                    <?= _('weiblich') ?>
                </label>
                <label>
                    <input type="radio" name="geschlecht" value="3"
                            <? if ($user->geschlecht == 3) echo 'checked'; ?>
                            <?= $disable_field ? 'disabled' : '' ?>>
                    <?= _('divers') ?>
                </label>
            </div>
        </section>
    </fieldset>



    <fieldset>
        <legend>
            <?= _('Registrierungsdaten') ?>
        </legend>


        <? if ($GLOBALS['perm']->have_perm('root')
               && Config::get()->ALLOW_ADMIN_USERACCESS
               && !StudipAuthAbstract::CheckField('auth_user_md5.password', $user->auth_plugin)
               && !$prelim
        ): ?>

            <label>
                <?= _('Neues Passwort') ?>
                <input class="user_form" name="pass_1" type="password" id="pass_1" autocomplete="new-password">
            </label>

            <label>
                <?= _('Passwortwiederholung') ?>

                <input class="user_form" name="pass_2" type="password" id="pass_2" autocomplete="new-password"
                       onkeyup="jQuery('#pw_success').toggle(jQuery('#pass_1').val() === $('#pass_2').val())">
           </label>

           <label class="col-2">
                <?= Icon::create('accept', 'accept')->asImg([
                    'id'    => 'pw_success',
                    'style' => 'display: none',
                ]) ?>
            </label>
        <? endif; ?>


        <section>
            <label for="email" <? if (!$prelim) echo 'class="required"'; ?>>
                <?= _('E-Mail') ?>
            </label>

            <? if (StudipAuthAbstract::CheckField('auth_user_md5.Email', $user->auth_plugin) || LockRules::check($user->user_id, 'email')) : ?>
                <input class="user_form" type="email" id="email"
                       value="<?= htmlReady($user['Email']) ?>" <? if (!$prelim) echo 'required'; ?> readonly>
            <? else : ?>
                <input class="user_form" type="email" name="Email" id="email"
                       value="<?= htmlReady($user['Email']) ?>" <? if (!$prelim) echo 'required'; ?>>
                <? if ($GLOBALS['MAIL_VALIDATE_BOX']) : ?>
                    <label>
                        <input type="checkbox" name="disable_mail_host_check" value="1">
                        <?= _('Mailboxüberprüfung deaktivieren') ?>
                    </label>
                <? endif ?>
            <? endif ?>
        </section>

        <label>
            <?= _('Authentifizierung') ?>

            <select name="auth_plugin" id="auth_plugin">
            <? foreach ($available_auth_plugins as $key => $val): ?>
                <option value="<?= $key ?>" <? if (strcasecmp($key, $user->auth_plugin) == 0) echo 'selected'; ?>>
                    <?= htmlReady($val) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        
        <section class="col-2">
            <label for="expiration_date">
                <?= _('Ablaufdatum') ?>
            </label>

            <div class="hgroup">
                <input class="user_form" type="text"
                       name="expiration_date" id="expiration_date"
                       data-date-picker
                       value="<? if (UserConfig::get($user->user_id)->EXPIRATION_DATE) echo strftime('%x', UserConfig::get($user->user_id)->EXPIRATION_DATE); ?>">

                <label>
                    <input type="checkbox" onchange="jQuery('input[name=expiration_date]').val('');"
                           name="expiration_date_delete" value="1">
                    <?= _('löschen') ?>
                </label>
            </div>
        </section>

        <? if ($user->validation_key|| true) : ?>
        <section class="col-3">
            <span class="label-text">
                <?= _('Validation-Key') ?>
            </span>

            <div class="hgroup">
                <?= htmlReady($user->validation_key) ?>

                <label>
                    <input type="checkbox" name="delete_val_key" value="1">
                    <?= _('löschen') ?>
                </label>
            </div>
        </section>
        <? endif ?>


        <section class="col-2">
            <span class="label-text">
                <?= _('Zuletzt aktiv') ?>
            </span>

            <br>

            <? if ($user->online->last_lifesign): ?>
                <abbr title="<?= strftime('%x %X', $user->online->last_lifesign) ?>">
                    <?= reltime($user->online->last_lifesign, true, 2) ?>
                </abbr>
            <? else: ?>
                <?= _('nie benutzt') ?>
            <? endif; ?>
        </section>

        <section class="col-2">
            <span class="label-text">
                <?= _('Registriert seit') ?>
            </span>

            <br>

            <? if ($user->mkdate): ?>
                <?= strftime('%x', $user->mkdate) ?>
            <? else: ?>
                <?= _('unbekannt') ?>
            <? endif; ?>
        </section>
    </fieldset>

    <? if (in_array($user->perms, ['autor', 'tutor', 'dozent'])): ?>
    <fieldset>
        <legend>
            <?= _('Studiendaten') ?>
        </legend>

        <? if (!StudipAuthAbstract::CheckField('studiengang_id', $user->auth_plugin)) : ?>
            <section class="col-3">
                <span class="label-text"><?= _('Neuer Studiengang') ?></span>

                <div class="hgroup">
                    <select style="width: 30%" name="new_studiengang" id="new_studiengang" aria-label="<?= _('-- Bitte Fach auswählen --')?>">
                        <option selected value="none"><?= _('-- Bitte Fach auswählen --')?></option>
                        <? foreach ($faecher as $fach) :?>
                            <?= sprintf('<option value="%s">%s</option>', $fach->id, htmlReady(my_substr($fach->name, 0, 50)));?>
                        <? endforeach?>
                    </select>

                    <select style="width: 30%" name="new_abschluss" id="new_abschluss" aria-label="<?= _('-- Bitte Abschluss auswählen --')?>">
                        <option selected value="none"><?= _('-- Bitte Abschluss auswählen --')?></option>
                        <? foreach ($abschluesse as $abschluss) :?>
                            <?= sprintf('<option value="%s">%s</option>' . "\n", $abschluss->id, htmlReady(my_substr($abschluss->name, 0, 50)));?>
                        <? endforeach?>
                    </select>

                    <select name="fachsem" aria-label="<?= _("Bitte Fachsemester wählen") ?>">
                        <? for ($i = 1; $i <= 50; $i += 1): ?>
                            <option><?= $i ?></option>
                        <? endfor; ?>
                    </select>
                </div>
            </section>
        <? endif ?>
        <label class="col-3">
            <?= _('Neue Einrichtung') ?>

            <select name="new_student_inst" id="new_student_inst" class="nested-select">
                <option value="" class="is-placeholder">
                    <?= _('-- Bitte Einrichtung auswählen --') ?>
                </option>
                <? foreach ($available_institutes as $i) : ?>

                    <? if (InstituteMember::countBySql('user_id = ? AND institut_id = ?', [$user->user_id, $i['Institut_id']]) == 0
                        && (!($i['is_fak'] && $user->perms == 'admin') || $GLOBALS['perm']->have_perm('root'))
                    ) : ?>
                        <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                                value="<?= htmlReady($i['Institut_id']) ?>">
                            <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                        </option>
                    <? else: ?>
                        <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>" disabled>
                            <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                        </option>
                    <? endif; ?>
                <? endforeach; ?>
            </select>
        </label>

        <? if (sizeof($user->studycourses)) : ?>
        <section class="col-3">
            <ol class="default">
            <? foreach ($user->studycourses as $i => $usc) : ?>
                <li>
                    <?= sprintf(
                        '%s, %s, %s. %s',
                        htmlReady($usc->studycourse->name),
                        htmlReady($usc->degree->name),
                        htmlReady($usc->semester),
                        _('Fachsemester')
                    ) ?>
                    <a href="<?= $controller->url_for('admin/user/delete_studycourse/' . $user->user_id . '/' . $usc->fach_id . '/' . $usc->abschluss_id) ?>">
                        <?= Icon::create('trash')->asImg([
                            'class' => 'text-bottom',
                            'title' => _('Diesen Studiengang löschen'),
                        ]) ?>
                    </a>
                    <? $versionen = StgteilVersion::findByFachAbschluss($usc->fach_id, $usc->abschluss_id); ?>
                    <? $versionen = array_filter($versionen, function ($ver) {
                        return $ver->hasPublicStatus('genehmigt');
                    }); ?>
                    <? if (count($versionen)): ?>
                        <br>
                        <select name="change_version[<?= $usc->fach_id ?>][<?= $usc->abschluss_id ?>]"
                                aria-labelledby="version_label">
                            <option value=""><?= _('-- Bitte Version auswählen --') ?></option>
                        <? foreach ($versionen as $version) : ?>
                            <option <? if ($version->getId() == $usc->version_id) echo 'selected'; ?>
                                    value="<?= htmlReady($version->getId()) ?>">
                                <?= htmlReady($version->getDisplayName()) ?>
                            </option>
                        <? endforeach; ?>
                        </select>
                    <? else : ?>
                        <?= tooltipIcon(_('Keine Version in der gewählten Fach-Abschluss-Kombination verfügbar.'), true) ?>
                    <? endif; ?>
                </li>
            <? endforeach ?>
            </ol>
        </section>
        <? endif ?>

        <? if (isset($student_institutes) && count($student_institutes)) : ?>
        <section class="col-3">
            <ol class="default">
            <? foreach ($student_institutes as $i => $inst_membership) : ?>
                <li>
                    <?= htmlReady($inst_membership->institute->name) ?>

                    <? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_membership->institut_id)) : ?>
                        <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                            <?= Icon::create('trash')->asImg([
                                'class' => 'text-bottom',
                                'title' => _('Diese Einrichtung löschen'),
                            ]) ?>
                        </a>
                    <? endif; ?>
                </li>
            <? endforeach; ?>
            </ol>
        </section>
        <? endif; ?>
    <? endif; ?>
    </fieldset>

    <? if ($user['perms'] !== 'root'): ?>
    <fieldset>
        <legend>
            <?= _('Einrichtungsdaten') ?>
        </legend>

        <label class="col-3">
            <?= _('Neue Einrichtung') ?>

            <select name="new_inst[]" id="new_inst" class="nested-select" multiple>
                <option value="" class="is-placeholder">
                    <?= _('-- Bitte Einrichtung auswählen --') ?>
                </option>
            <? foreach ($available_institutes as $i) : ?>
                <? if (InstituteMember::countBySql('user_id = ? AND institut_id = ?', [$user->user_id, $i['Institut_id']]) == 0
                       && (!($i['is_fak'] && $user->perms == 'admin') || $GLOBALS['perm']->have_perm('root'))
                ) : ?>
                    <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                            value="<?= htmlReady($i['Institut_id']) ?>">
                        <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                    </option>
                <? else: ?>
                    <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>" disabled>
                        <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                    </option>
                <? endif; ?>
            <? endforeach; ?>
            </select>
        </label>
        <? if (isset($institutes) && count($institutes)) : ?>
        <section class="col-3">
            <ol class="default">
            <? foreach ($institutes as $i => $inst_membership) : ?>
                <li>
                    <?= htmlReady($inst_membership->institute->name) ?>

                    <? if ($GLOBALS['perm']->have_studip_perm("admin", $inst_membership->institut_id)) : ?>
                        <a data-dialog="size=auto"
                           href="<?= $controller->url_for('admin/user/edit_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                            <?= Icon::create('edit')->asImg([
                                'class' => 'text-bottom',
                                'title' => _('Diese Einrichtung bearbeiten'),
                            ]) ?>
                        </a>
                        <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                            <?= Icon::create('trash')->asImg([
                                'class' => 'text-bottom',
                                'title' => _('Diese Einrichtung löschen'),
                            ]) ?>
                        </a>
                    <? endif; ?>
                </li>
            <? endforeach; ?>
            </ol>
        </section>
        <? endif;?>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Nutzerdomänen') ?>
        </legend>

        <? if (!empty($domains)) : ?>
        <label class="col-3">
            <?= _('Neue Nutzerdomäne') ?>

            <select name="new_userdomain" id="new_userdomain">
                <option selected value="none"><?= _('-- Bitte Nutzerdomäne auswählen --') ?></option>
            <? foreach ($domains as $domain) : ?>
                <option value="<?= $domain->id ?>">
                    <?= htmlReady(my_substr($domain->name, 0, 50)) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
        <? endif ?>

        <? if (count($userdomains) > 0): ?>
        <section class="col-3">
            <ol class="default">
            <? foreach ($userdomains as $i => $domain): ?>
                <li>
                    <?= htmlReady($domain->name) ?>

                    <a href="<?= $controller->url_for('admin/user/delete_userdomain/' . $user->id, ['domain_id' => $domain->id]) ?>">
                        <?= Icon::create('trash')->asImg([
                            'class' => 'text-bottom',
                            'title' => _('Aus dieser Nutzerdomäne austragen'),
                        ]) ?>
                    </a>
                </li>
            <? endforeach; ?>
            </ol>
        </section>
        <? endif; ?>
    </fieldset>
    <? endif;  /* $user['perms'] !== 'root' */ ?>

    <? if ($GLOBALS['perm']->have_perm('root') && count(LockRule::findAllByType('user')) > 0) : ?>
    <fieldset>
        <legend>
            <?= _('Sperrebene') ?>
        </legend>

        <label>
            <?= _('Sperrebene') ?>

            <select name="lock_rule" id="lock_rule">
                <option value="none">
                    <?= _('-- Bitte Sperrebene auswählen --') ?>
                </option>
            <? foreach (LockRule::findAllByType('user') as $rule) : ?>
                <option value="<?= $rule->getId() ?>" <? if ($user['lock_rule'] == $rule->getId()) echo 'selected'; ?>>
                    <?= htmlReady($rule->name) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>
    <? endif ?>

    <? if (count($userfields) > 0) : ?>
    <fieldset>
        <legend>
            <?= _('Datenfelder') ?>
        </legend>

        <? foreach ($userfields as $entry) : ?>
            <? if ($entry->isVisible()) : ?>
                <? $text = $entry->isVisible($user->perms)
                    ? ''
                    : _('Systemfeld (für die Person selbst nicht sichtbar)') ?>

                <? if ($entry->isEditable() && !LockRules::Check($user->user_id, $entry->getId())) : ?>
                    <section class="col-3">
                    <?= $entry->getHTML('datafields', ['tooltip' => $text]) ?>
                </section>
                <? else : ?>
                    <section class="col-3">
                        <?= htmlReady($entry->getName()) ?> <?= $text ? tooltipIcon($text) : '' ?><br>
                        <?= $entry->getDisplayValue() ?: '<span class="empty">'. _('keine Angabe') .'</span>' ?>
                    </section>
                <? endif ?>
            <? endif ?>
        <? endforeach ?>
    </fieldset>
    <? endif ?>

    <footer>
        <label>
            <input name="u_edit_send_mail" value="1" checked type="checkbox">
            <?= _('E-Mail-Benachrichtigung bei Änderung der Daten verschicken?') ?>
        </label>
        <br>

        <?= Button::createAccept(_('Speichern'), 'edit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user'), ['name' => 'abort']) ?>
    </footer>
</form>
