<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<form action="<?= $controller->url_for('admin/datafields/edit/' . $item->id) ?>" method="post"
      class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Bearbeiten der Parameter') ?></legend>

        <label>
            <span class="required"><?= _('Name') ?></span>

            <?= I18N::input('datafield_name', $item->name, [
                'id'        => 'datafield_name',
                'required'  => '',
                'size'      => 60,
                'maxlength' => 254,
            ]) ?>
        </label>

        <label>
            <?= _('Feldtyp') ?>

            <select name="datafield_type" id="datafield_type">
            <? foreach (DataFieldEntry::getSupportedTypes($item->object_type) as $param): ?>
                <option <? if ($item->type === $param) echo 'selected'; ?>>
                     <?= htmlReady($param) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
        <? if (!$datafield_entry instanceof DataFieldI18NEntry): ?>
            <?= str_replace('['.$datafield_id.']', '', $datafield_entry->getHTML('default_value')) ?>
        <? endif ?>
        <label>
        <? if ($item->object_type === 'sem'): ?>
            <?= _('Veranstaltungskategorie') ?>:

            <select name="object_class[]" id="object_class">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach (SemClass::getClasses() as $key => $val): ?>
                <option value="<?= $key ?>" <? if ($item->object_class == $key) echo 'selected'; ?>>
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? elseif ($item->object_type === 'inst'): ?>
            <?= _('Einrichtungstyp') ?>:

            <select name="object_class[]" id="object_class">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach ($GLOBALS['INST_TYPE'] as $key => $val): ?>
                <option value="<?= $key ?>" <? if ($item->object_class == $key) echo 'selected'; ?>>
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? elseif ($item->object_type === 'moduldeskriptor'): ?>
            <?= _('Sprache') ?>:

            <select multiple name="object_class[]" id="object_class" required>
                <option value="NULL" <? if ($item->object_class === null) echo 'selected'; ?>><?= _('alle (mehrsprachige Eingabe bei Feldtyp textline, textarea, textmarkup)') ?></option>
            <? foreach ((array) $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'] as $key => $value) : ?>
                <option value="<?= htmlReady($key) ?>" <? if (mb_strpos($item->object_class, $key) !== false) echo 'selected'; ?>>
                    <?= htmlReady($value['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? elseif ($item->object_type === 'modulteildeskriptor'): ?>
            <?= _('Sprache') ?>:

            <select multiple name="object_class[]" id="object_class" required>
                <option value="NULL" <? if ($item->object_class === null) echo 'selected'; ?>><?= _('alle (mehrsprachige Eingabe)') ?></option>
            <? foreach ((array) $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'] as $key => $value) : ?>
                <option value="<?= htmlReady($key) ?>" <? if (mb_strpos($item->object_class, $key) !== false) echo 'selected'; ?>>
                    <?= htmlReady($value['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? else : ?>
            <?= _('Nutzerstatus') ?>:

            <select multiple size="<?= count($controller->user_status) ?>" name="object_class[]" id="object_class" required>
                <option value="0" <? if ($item->object_class === null) echo 'selected'; ?>>
                    <?= _('alle') ?>
                </option>
            <? foreach ($controller->user_status as $key => $value): ?>
                <option value="<?= $value ?>" <? if ($item->object_class & DataField::permMask($key)) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        <? endif; ?>
        </label>

        <label>
            <?= _('Benötigter Status zum Bearbeiten') ?>

            <select name="edit_perms" id="edit_perms">
            <? foreach (array_keys($controller->user_status) as $key): ?>
                <option <? if ($item->edit_perms === $key) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Sichtbarkeit') ?> (<?= _('für andere') ?>)

            <select name="visibility_perms" id="visibility_perms">
                <option value="all" <? if ($item->view_perms == 'all') echo 'selected'; ?>>
                    <?= _('alle') ?>
                </option>
            <? foreach (array_keys($controller->user_status) as $key): ?>
                <option <? if ($item->view_perms === $key) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

    <? if ($item->object_type === 'user'): ?>
        <label>
            <?= _('Systemfeld') ?>
            <?= tooltipIcon(_('Nur für die Person selbst sichtbar, wenn der '
                            . 'benötigte Status zum Bearbeiten oder die '
                            . 'Sichtbarkeit ausreichend ist')) ?>

            <input type="hidden" name="system" value="0">
            <input type="checkbox" name="system" value="1"
                   <? if ($item->system) echo 'checked'; ?>>
        </label>
    <? endif; ?>

        <label>
            <?= _('Position') ?>

            <input type="text" name="priority" id="priority"
                   maxlength="10" size="5"
                   value="<?= $item->priority ?>">
           </td>
        </label>

    <? if ($item->object_type === 'sem') : ?>
        <label>
            <input type="checkbox" name="is_required" id="is_required" value="1"
                   <? if ($item->is_required) echo 'checked'; ?>>
           <?= _('Eintrag verpflichtend') ?>
        </label>

        <label>
            <?= _('Beschreibung') ?>:

            <textarea name="description" id="description"><?= htmlReady($item->description) ?></textarea>
        </label>
    <? endif; ?>

    <? if ($item->object_type === 'user'): ?>
        <label>
            <?= _('Mögliche Bedingung für Anmelderegel') ?>:

            <input type="checkbox" name="is_userfilter" id="is_userfilter" value="1"
                   <? if ($item->is_userfilter) echo 'checked'; ?>>
        </label>
    <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen übernehmen')])?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields/index/'.$item->type.'#'.$item->type), ['title' => _('Zurück zur Übersicht')])?>
    </footer>
</form>
