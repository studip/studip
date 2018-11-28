<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('admin/user/store_user_institute/' . $institute->user_id . '/' . $institute->institut_id) ?>"
      method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= $user->getFullName() ?> - 
            <?= _('Bearbeiten der Einrichtungsdaten') ?>
        </legend>

        <label>
            <?= _('Status') ?>
            <select name="inst_perms">
                <? foreach ($perms as $perm) : ?>
                    <option value="<?= htmlReady($perm) ?>" <? if ($institute->inst_perms === $perm) echo 'selected'; ?>>
                        <?= htmlReady($perm) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Raum') ?>
            <input type="text" name="raum" value="<?= htmlReady($institute->raum) ?>">
        </label>
        <label>
            <?= _('Sprechzeiten') ?>
            <input type="text" name="sprechzeiten" value="<?= htmlReady($institute->sprechzeiten) ?>">
        </label>
        <label>
            <?= _('Telefon') ?>
            <input type="text" name="telefon" value="<?= htmlReady($institute->Telefon) ?>">
        </label>
        <label>
            <?= _('Fax') ?>
            <input type="text" name="fax" value="<?= htmlReady($institute->Fax) ?>">
        </label>
        <label>
            <input type="checkbox" name="externdefault" value="1" <? if ($institute->externdefault) echo 'checked'; ?>>
            <?= _('Standard-Adresse') ?>
        </label>
        <label>
            <input type="checkbox" name="visible" value="1" <? if ($institute->visible) echo 'checked'; ?>>
            <?= _('Auf der Profilseite und in Adressbüchern sichtbar') ?>
        </label>

        <? if (count($datafields) > 0) : ?>
            <? foreach ($datafields as $entry) : ?>
                <? if ($entry->isEditable()): ?>
                    <?= $entry->getHTML('datafields') ?>
                <? else : ?>
                    <section>
                        <?= htmlReady($entry->getName()) ?>
                        <?= $entry->getDisplayValue() ?: '<span class="empty">'. _('keine Angabe') .'</span>' ?>
                    </section>
                <? endif ?>
            <? endforeach ?>
        <? endif ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen übernehmen')]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/edit/' . $user->user_id)) ?>
    </footer>
</form>
