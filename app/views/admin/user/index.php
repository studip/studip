<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>


<form action="<?= $controller->url_for('admin/user') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Benutzerverwaltung') ?></legend>

        <label class="col-3">
            <?= _('Benutzername') ?>
            <input name="username" type="text" value="<?= htmlReady($request['username']) ?>">
        </label>

        <label class="col-3">
            <?= _('E-Mail') ?>
            <input name="email" type="text" value="<?= htmlReady($request['email']) ?>">
        </label>

        <label class="col-3">
            <?= _('Vorname') ?>
            <input name="vorname" type="text" value="<?= htmlReady($request['vorname']) ?>">
        </label>

        <label class="col-3">
            <?= _('Nachname') ?>
            <input name="nachname" type="text" value="<?= htmlReady($request['nachname']) ?>">
        </label>

        <label class="col-2">
            <?= _('Status')?>

            <select name="perm">
            <? foreach(words('alle user autor tutor dozent admin root') as $one): ?>
                <option value="<?= $one ?>" <?= ($request['perm'] === $one) ? 'selected' : '' ?>>
                    <?= ($one === 'alle') ? _('alle') : $one ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-2">
            <span class="label-text"><?= _('inaktiv') ?></span>

            <div class="hgroup">
                <select name="inaktiv" class="size-s">
                <? foreach(['<=' => '>=', '=' => '=', '>' => '<', 'nie' =>_('nie')] as $i => $one): ?>
                    <option value="<?= htmlready($i) ?>" <?= ($request['inaktiv'][0] === $i) ? 'selected' : '' ?>>
                        <?= htmlReady($one) ?>
                    </option>
                <? endforeach; ?>
                </select>

                <input name="inaktiv_tage" type="number" id="inactive"
                       value="<?= htmlReady($request['inaktiv'][1]) ?>">
                <?= _('Tage') ?>
            </div>
        </label>

        <label class="col-2" style="padding-top: 1.8em;">
            <input type="checkbox" name="locked" value="1" <?=  ($request['locked']) ?  'checked' : '' ?>>
            <?= _('nur gesperrt') ?>
        </label>
    </fieldset>

    <fieldset class="collapsable <?= (!$advanced) ?  'collapsed' : '' ?>">
        <legend><?= _('Erweiterte Suche') ?></legend>

        <label for="institute" class="col-3">
            <?=_('Einrichtung')?>
            <select name="institute">
                <option value=""><?= _('Alle')?></option>
                <? foreach($institutes as $institute) : ?>
                    <option value="<?= $institute['Institut_id']?>" <?= $request['institute'] == $institute['Institut_id'] ? 'selected' : ''?>>
                        <?= htmlReady($institute['Name'])?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <label class="col-3">
            <?= _('Nutzerdomäne') ?>

            <select name="userdomains">
                <option value=""><?= _('Alle') ?></option>
                <option value="null-domain" <?= $request['userdomains'] === 'null-domain' ? 'selected' : '' ?>>
                    <?= _('Ohne Domäne') ?>
                </option>
            <? foreach ($userdomains as $one): ?>
                <option value="<?= htmlReady($one->id) ?>" <?= $request['userdomains'] === $one->id ? 'selected' : ''?>>
                    <?= htmlReady($one->name ?: $one->id) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-3">
            <?=_('Abschluss')?>
            <select name="degree[]" multiple class="nested-select">
                <option value=""><?=_('Alle')?></option>
                <? foreach($degrees as $degree) : ?>
                    <option value="<?= $degree->id ?>"
                        <?= !empty($request['degree']) && in_array($degree->id, $request['degree']) ? 'selected' : ''?>>
                        <?=htmlReady($degree->name)?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <label class="col-3">
            <?=_('Fach')?>
            <select name="studycourse[]" multiple class="nested-select">
                <option value=""><?=_('Alle')?></option>
                <? foreach($studycourses as $studycourse) : ?>
                    <option value="<?= $studycourse->id ?>"
                        <?= !empty($request['studycourse']) && in_array($studycourse->id, $request['studycourse']) ? 'selected' : ''?>>
                        <?=htmlReady($studycourse->name)?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        
        <label class="col-3">
            <?= _('Fachsemester') ?>
            <select name="fachsem">
                <option value=""><?= _('Alle') ?></option>
                <? for ($i = 1; $i <= 50; $i += 1): ?>
                    <option <?= $request['fachsem'] && (int)$request['fachsem'] === $i ? 'selected' : ''?>>
                        <?= $i ?>
                    </option>
                <? endfor; ?>
            </select>
        </label>

        <label class="col-3">
            <?= _('Authentifizierung') ?>
            <select name="auth_plugins">
               <option value=""><?= _('Alle') ?></option>
           <? foreach (array_merge(['preliminary' => _('vorläufig')], $available_auth_plugins) as $key => $val): ?>
                <option value="<?= $key ?>" <?= $request['auth_plugins'] === $key ? 'selected' : '' ?>>
                    <?= htmlReady($val) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

    <? foreach ($datafields as $datafield): ?>
        <label class="col-3">
            <?= htmlReady($datafield->name) ?>

        <? if ($datafield->type === 'bool'): ?>
            <section class="hgroup">
                <span class="col-2">
                    <input type="radio" name="<?= $datafield->id ?>" value="" <?= (mb_strlen($request[$datafield->id]) === 0) ? 'checked' : '' ?>>
                    <?= _('egal') ?>
                </span>
                <span class="col-2">
                    <input type="radio" name="<?= $datafield->id ?>" value="1" <?= ($request[$datafield->id] === '1') ? 'checked' : '' ?>>
                    <?= _('ja') ?>
                </span>
                <span class="col-2">
                    <input type="radio" name="<?= $datafield->id ?>" value="0" <?= ($request[$datafield->id] === '0') ? 'checked' : '' ?>>
                    <?= _('nein') ?>
                </span>
            </section>
        <? elseif ($datafield->type === 'selectbox' || $datafield->type === 'radio') : ?>
            <? $datafield_entry = DataFieldEntry::createDataFieldEntry($datafield);?>
            <select name="<?= $datafield->id ?>">
                <option value="---ignore---"><?= _('alle') ?></option>
            <? foreach ($datafield_entry->type_param as $pkey => $pval) :?>
                <? $value = $datafield_entry->is_assoc_param ? (string) $pkey : $pval; ?>
                <option value="<?= $value ?>" <?= ($request[$datafield->id] === $value) ? 'selected' : '' ?>>
                    <?= htmlReady($pval) ?>
                </option>
            <? endforeach ?>
            </select>
        <? else : ?>
            <input type="text" name="<?= $datafield->id ?>" value="<?= htmlReady($request[$datafield->id]) ?>">
        <? endif ?>
        </label>
    <? endforeach; ?>
        <? if(!empty($roles)) :?>
            <label class="col-3">
                <?= _('Rollen')?>
                <select name="roles[]" multiple class="nested-select">
                    <option value=""><?=_('Alle')?></option>
                    <? foreach($roles as $role) : ?>
                        <option value="<?= $role->roleid ?>" <?=  !empty($request['roles']) && in_array($role->roleid, $request['roles']) ? 'selected' : '' ?>>
                            <?= htmlReady($role->rolename) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        <? endif?>
        <label>
            <input type="checkbox" name="show_only_not_lectures" value="1"
                   <? if ($request['show_only_not_lectures']) echo 'checked'; ?>>
            <?= _('Nur Personen anzeigen, die in keiner Veranstaltung Lehrende sind')?>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Suchen'), 'search')?>
        <?= Button::create(_('Zurücksetzen'), 'reset')?>
    </footer>
</form>

<? if (!empty($users) && is_array($users)): ?>
    <?= $this->render_partial('admin/user/_results') ?>
<? endif; ?>
