<label><?= _('Für alle Veranstaltungen') ?>
    <select name="lock_sem_all" style="max-width: 200px">
        <? foreach ($aux_lock_rules as $id => $rule) : ?>
            <option value="<?= $id ?>"
                <?= ($values['aux_lock_rule'] == $id) ? 'selected' : '' ?>>
                <?= htmlReady($rule["name"]) ?>
            </option>
        <? endforeach ?>
    </select>
</label>
<label>
<input type="checkbox" value="1" name="aux_all_forced">
<?=_("Erzwungen")?>
</label>
<?= \Studip\Button::createAccept(_('Speichern'), 'all'); ?>