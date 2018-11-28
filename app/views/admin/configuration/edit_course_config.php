<h2 class="hide-in-dialog">
    <?= _('Bearbeiten von Konfigurationsparameter für die Veranstaltung: ') ?>
    <?= htmlReady(Course::find($range_id)->getFullname()) ?>
</h2>

<form action="<?= $controller->url_for('admin/configuration/edit_course_config/' . $range_id . '?id=' . $field) ?>" method="post" data-dialog class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= htmlReady($field) ?>
        </legend>
        <?= $this->render_partial('admin/configuration/type-edit.php', $config) ?>
        <label>
            <?= _('Beschreibung:') ?> (<em>description</em>)
            <textarea name="descriptio" readonly><?= htmlReady($config['description']) ?></textarea>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('admin/configuration/course_configuration', compact('range_id'))) ?>
    </footer>
</form>
