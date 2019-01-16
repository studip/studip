<form class="default" action="<?= $controller->link_for("admin/semester/lock/{$id}") ?>" method="post" data-dialog="size=auto" class="default">

    <fieldset>
        <legend><?= _('Berechtigungen') ?></legend>

        <label>
            <input name ="degrade_users" type="checkbox" value="1" checked>
            <?= _('Teilnehmende zu Lesern herabstufen') ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Anmelderegeln') ?></legend>

        <label>
            <input name ="lock_enroll" type="checkbox" value="1">
            <?= _('Anmeldung gesperrt') ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Zusätzliche optionale Sperrebene') ?></legend>

        <label>
            <?= _('Für alle Veranstaltungen') ?>
            <select name="lock_sem_all">
                <option value="">
                    -- <?= _('keine Sperrebene')  ?> --
                </option>
            <? foreach ($all_lock_rules as $lock_rule): ?>
                <option value="<?= htmlReady($lock_rule->id) ?>">
                    <?= htmlReady($lock_rule->name) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Sperren'), 'confirm_lock'); ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('admin/semester'))?>
    </footer>
</form>
